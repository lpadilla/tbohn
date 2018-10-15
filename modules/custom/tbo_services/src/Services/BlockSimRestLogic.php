<?php

namespace Drupal\tbo_services\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_mail\SendMessageInterface;
use Drupal\rest\ResourceResponse;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class BlockSimRestLogic.
 *
 * @package Drupal\tbo_services
 */
class BlockSimRestLogic {
  protected $api;
  protected $send;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;
  protected $message;
  protected $data;

  /**
   * BlockSimRestLogic constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Config.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Api.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user.
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Send message.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api, AccountInterface $currentUser, SendMessageInterface $sendMessage) {
    $this->api = $api;
    $this->send = $sendMessage;
    $this->tboConfig = $tboConfig;
    $this->currentUser = $currentUser;
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();

    // Init all messages.
    $this->message = [
      'EventType' => t('Servicios'),
      'Description' => t('Bloqueo de SIM Card por @reason exitoso'),
      'DescriptionError' => t('Bloqueo de SIM Card no exitoso'),
      'detailsSuccess' => t('Usuario @user bloqueo correctamente por @reason la SIM Card de la línea @msisdn con IMSI @imsi asociada al contrato @contractId.'),
      'detailsError' => t('Usuario @user no pudo bloquear correctamente la SIM Card de la línea @msisdn con IMSI @imsi asociada al contrato @contractId.'),
      'detailsErrorAdmin' => t('Usuario @user no pudo bloquear correctamente la SIM Card de la línea @msisdn con IMSI @imsi asociada al contrato @contractId. El error retornado por el servicio web a consumir fue @codeError y descripción "@messageError".'),
      'NoDisponible' => t('No disponible'),
      'messageSuccess' => t('Se ha bloqueado correctamente por @reason la SIM Card de la línea @msisdn.'),
      'MessageError' => t('Se ha presentado una falla en la comunicación con el servicio tolBlockUnlock, por favor intente más tarde.'),
    ];
  }

  /**
   * Implements get().
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function get() {
    // Prevent caching.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Validate permission.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $user = User::load($this->currentUser->id());

    $response = [
      'document' => isset($user->get('document_number')->value) ? $user->get('document_number')->value : '',
      'documentType' => isset($user->get('document_type')->value) ? $user->get('document_type')->value : '',
      'msisdn' => $_SESSION['serviceDetail']['address'],
      'company' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'companyDocumentType' => $_SESSION['company']['docType'],
      'contractId' => $_SESSION['serviceDetail']['contractId'],
    ];

    return new ResourceResponse($response);
  }

  /**
   * Implements post().
   *
   * @param mixed $data
   *   Data.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function post($data) {
    // Prevent caching.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $this->data = $data;

    // Validate permission.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Valid mobile services.
    if (!$this->validMsisdn()) {
      throw new AccessDeniedHttpException();
    }

    try {
      return $this->blockSim();
    }
    catch (\Exception $error) {
      \Drupal::logger('Block Sim Card')->error($error->getMessage() . '<br>' . $error->getTraceAsString());
      return $this->responseError($error);
    }
  }

  /**
   * Implements validMsisdn().
   *
   * @return bool
   *   Resultado de la solicitud.
   */
  public function validMsisdn() {
    $result = FALSE;
    $quantity = 20;
    $params['query'] = [
      'id' => $this->data['companyDocument'],
      'idType' => $this->data['companyDocumentType'],
      'businessUnit' => 'B2B',
      'offset' => 1,
      'limit' => $quantity,
    ];

    while ($quantity == $params['query']['limit']) {
      $quantity = 0;
      $lines = [];

      $lines = $this->api->GetLineDetailsbyDocumentId($params);

      if (isset($lines->lineCollection)) {
        foreach ($lines->lineCollection as $line) {
          if ($line->msisdn == $this->data['msisdn']) {
            $this->data['imsi'] = $line->imsi;
            $result = TRUE;
            $quantity = 0;
          }

          $quantity++;
        }
      }

      $params['query']['offset'] = $params['query']['offset'] + $quantity;
    }

    return $result;
  }

  /**
   * Implements blockSim().
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function blockSim() {
    $params['tokens'] = [
      'msisdn' => $this->data['msisdn'],
    ];
    $params['query'] = [
      'operation' => 'BLOCK',
      'blockUnlockType' => 'SIM',
      'reason' => 'EXTRAVIO',
    ];
    $paramsBody = [
      'name' => $this->data['company'],
      'phoneNumber' => $this->data['msisdn'],
      'address' => '',
      'documentNumber' => $this->data['document'],
      'documentType' => $this->data['documentType'],
      'city' => '',
      'department' => '',
      'description' => 'Block SIM',
      'source' => 'TOLC',
    ];
    $params['body'] = json_encode($paramsBody);

    $result = $this->api->tolBlockUnlock($params)->result;

    if ($result->success) {
      // Se elimina caché generada para la data del portafolio de servicios.
      BaseApiCache::invalidateTags([
        'GetLineDetailsbyDocumentId:' . $this->data['companyDocument'] . ':' . $this->data['companyDocumentType'],
        'getByAccountDataUsingContract:' . $this->data['contractId'],
      ]);
      // Por último se cambia manualmente el estado de la línea.
      $details = $_SESSION['serviceDetail'];
      $details['status'] = $result->accountStatus;
      $_SESSION['serviceDetail'] = $details;
      return $this->responseSuccess();
    }
    else {
      return $this->responseError();
    }
  }

  /**
   * Implements responseSuccess().
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function responseSuccess() {
    $user = User::load($this->currentUser->id());
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    $userName = $service->getName();

    // Token log.
    $response = [
      'error' => FALSE,
    ];

    // Token log.
    $tokens = [
      '@user' => $userName,
      '@reason' => 'perdida',
      '@msisdn' => $this->data['msisdn'],
      '@imsi' => $this->data['imsi'],
      '@contractId' => $this->data['contractId'],
    ];

    // Log on success.
    $log = [
      'companyName' => $this->data['company'],
      'companyDocument' => $this->data['companyDocument'],
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => $this->message['EventType'],
      'description' => t('Bloqueo de SIM Card por @reason exitoso', $tokens),
      'details' => t('Usuario @user bloqueo correctamente por @reason la SIM Card de la línea @msisdn con IMSI @imsi asociada al contrato @contractId.', $tokens),
      'old_value' => $this->message['NoDisponible'],
      'new_value' => $this->message['NoDisponible'],
    ];

    // Save audit log.
    $service->insertGenericLog($log);

    // Send mail.
    $mailTokens = [
      'userName' => $userName,
      'userChange' => $userName,
      'msisdn' => $this->data['msisdn'],
      'imsi' => $this->data['imsi'],
      'contractId' => $this->data['contractId'],
      'company' => $this->data['company'],
      'companyDocumentType' => $this->data['companyDocumentType'],
      'companyDocument' => $this->data['companyDocument'],
      'reason' => 'Perdida',
      'mail' => $user->get('mail')->value,
    ];

    $this->sendMail($mailTokens);
    $users = \Drupal::service('tbo_services.tbo_services_repository')->getAllTigoAdmins();

    // Send message.
    foreach ($users as $key => $value) {
      $mailTokens['userName'] = (isset($value->full_name) && !empty($value->full_name)) ? $value->full_name : $value->name;
      $mailTokens['mail'] = $value->mail;

      if ($user->get('mail')->value != $value->mail) {
        $this->sendMail($mailTokens);
      }
    }

    // Send segment track.
    $this->sendSegmentTrack('Exitoso');

    $shortFormat = \Drupal::config('core.date_format.short')->get('pattern');
    $shortFormat = explode('-', $shortFormat);

    // Response.
    $response = [
      'error' => FALSE,
      'date' => date($shortFormat[0]),
      'hour' => date($shortFormat[1] . ' a'),
      'userName' => (empty($user->get('full_name')->value)) ? $this->currentUser->getAccountName() : $user->get('full_name')->value,
      'description' => t('Se ha bloqueado correctamente por @reason la SIM Card de la línea @msisdn.', $tokens),
      'state_modal' => $log['description'],
      'detail' =>  $log['description'],
    ];

    return new ResourceResponse($response);
  }

  /**
   * Implements responseError().
   *
   * @param mixed $error
   *   Exception.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function responseError($error = NULL) {
    $user = User::load($this->currentUser->id());
    $roles = $this->currentUser->getRoles();
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    // Token log.
    $tokens = [
      '@user' => $service->getName(),
      '@reason' => 'perdida',
      '@msisdn' => $this->data['msisdn'],
      '@imsi' => $this->data['imsi'],
      '@contractId' => $this->data['contractId'],
    ];

    // Add information to log if
    // the user has the role super_admin or tigo_admin.
    if (!is_null($error) && is_array($roles) && (in_array('super_admin', $roles) || in_array('admin_company', $roles) || in_array('tigo_admin', $roles))) {
      $error = UtilMessage::getMessage($error);
      $tokens['@codeError'] = $error['code'] != 0 ? $error['code'] : 400;
      $tokens['@messageError'] = !empty($error['message_error']) ? $error['message_error'] : 'bad request';
      $logDetail = t('Usuario @user no pudo bloquear correctamente la SIM Card de la línea @msisdn con IMSI @imsi asociada al contrato @contractId. El error retornado por el servicio web a consumir fue @codeError y descripción "@messageError".', $tokens);
    }
    else {
      $logDetail = t('Usuario @user no pudo bloquear correctamente la SIM Card de la línea @msisdn con IMSI @imsi asociada al contrato @contractId.', $tokens);
    }

    // Log on fail.
    $log = [
      'companyName' => $this->data['company'],
      'companyDocument' => $this->data['companyDocument'],
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => $this->message['EventType'],
      'description' => $this->message['DescriptionError'],
      'details' => $logDetail,
      'old_value' => $this->message['NoDisponible'],
      'new_value' => $this->message['NoDisponible'],
    ];

    // Save audit log.
    $service->insertGenericLog($log);

    // Send segment track.
    $this->sendSegmentTrack('Fallido');

    $shortFormat = \Drupal::config('core.date_format.short')->get('pattern');
    $shortFormat = explode('-', $shortFormat);

    // Response.
    $response = [
      'error' => TRUE,
      'date' => date($shortFormat[0]),
      'hour' => date($shortFormat[1] . ' a'),
      'userName' => (empty($user->get('full_name')->value)) ? $this->currentUser->getAccountName() : $user->get('full_name')->value,
      'description' => $this->message['MessageError'],
      'state_modal' => $this->message['DescriptionError'],
      'detail' =>  $log['description'],
    ];

    return new ResourceResponse($response);
  }

  /**
   * Implements sendMail().
   *
   * @param mixed $tokens
   *   Tokens.
   */
  public function sendMail($tokens) {
    try {
      $this->send->send_message($tokens, 'block_sim_card');
    }
    catch (\Exception $error) {
      \Drupal::logger('Block Sim Card')->error($error->getMessage() . '<br>' . $error->getTraceAsString());
    }
  }

  /**
   * Implements sendSegmentTrack().
   *
   * @param string $status
   *   Status.
   */
  public function sendSegmentTrack($status) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());

    if (isset($tigoId)) {
      try {
        $segment_track = [
          'event' => 'TBO - Bloqueo de Simcard - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => 'Movil - ' . $status . ' - movil',
          ],
        ];

        $this->segment->track($segment_track);
      }
      catch (\Exception $error) {
        \Drupal::logger('Block Sim Card')->error($error->getMessage() . '<br>' . $error->getTraceAsString());
      }
    }
  }

}
