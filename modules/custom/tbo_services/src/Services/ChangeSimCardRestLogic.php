<?php

namespace Drupal\tbo_services\Services;

use Drupal\user\Entity\User;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_mail\SendMessageInterface;
use Drupal\adf_core\Util\UtilMessage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ChangeSimCardRestLogic.
 *
 * @package Drupal\tbo_services\Services
 */
class ChangeSimCardRestLogic {

  protected $currentUser;

  protected $api;

  protected $sendMessage;

  protected $segment;

  /**
   * ChangeSimCardRestLogic constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user object.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Tbo Api client.
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Mail service.
   */
  public function __construct(AccountInterface $currentUser, TboApiClientInterface $api, SendMessageInterface $sendMessage) {
    $this->currentUser = $currentUser;
    $this->api = $api;
    $this->sendMessage = $sendMessage;
    \Drupal::service('adf_segment')->segmentPhpInit();
    $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
  }

  /**
   * Responds to GET request.
   *
   * @return \Drupal\rest\ResourceResponse|\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Service resource response.
   */
  public function get() {
    // Denied cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Validate user permission.
    if (!$this->currentUser->hasPermission('access content')) {
      return new AccessDeniedHttpException();
    }

    // Chane SIM Card.
    if (isset($_GET['change'])) {
      if (isset($_SESSION['serviceDetail']['address'])) {
        $oldValue = $this->getOldSim($_SESSION['serviceDetail']['address']);
      }

      // Get user data.
      $account = User::load($this->currentUser->id());
      $full_name = $account->get('full_name')->value;
      $mail = $account->getEmail();
      $new_num = $_GET['new_sim'];
      $num = $_SESSION['serviceDetail']['address'];

      // Set generic audit log.
      $details = [
        '@user' => (empty($full_name)) ? $this->currentUser->getAccountName() : $full_name,
        '@oldsim' => $oldValue,
        '@newsim' => $new_num,
        '@phone' => $num,
        '@contract' => $_SESSION['serviceDetail']['contractId'],
      ];

      $log_tokens = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de SIMCard exitoso'),
        'details' => t('Usuario @user cambio el número de SIMCard @oldsim por el nuevo número @newsim del servicio móvil de la línea @phone asociada al contrato @contract', $details),
        'old_values' => t('No aplica'),
        'new_values' => t('No aplica'),
      ];

      // Get data for pop-up.
      $bill_params['query'] = [
        'clientType' => strtoupper($_SESSION['company']['docType']),
        'countInvoiceToReturn' => 6,
        'endDate' => date('d/m/Y', time()),
        'type' => 'mobile',
        'contractNumber' => $_SESSION['serviceDetail']['contractId'],
      ];

      $bill_params['tokens'] = [
        'clientId' => $_SESSION['company']['nit'],
      ];

      try {
        $bill_info = $this->api->getBillingInformation($bill_params);
      }
      catch (\Exception $e) {
      }
      $bill_info = $bill_info->billingAccountCollection;

      foreach ($bill_info as $key => $value) {

        if ($value->billingAccountId == $_SESSION['serviceDetail']['contractId']) {
          $address = $value->billingAddressAccount->Street;
        }

      }
      $short_format = \Drupal::config('core.date_format.short')->get('pattern');
      $short_format = explode('-', $short_format);

      $return_data = [
        'userName' => (empty($full_name)) ? $this->currentUser->getAccountName() : $full_name,
        'enterpriseName' => $_SESSION['company']['name'],
        'enterpriseNumber' => $_SESSION['company']['nit'],
        'enterpriseDoc' => strtoupper($_SESSION['company']['docType']),
        'phone' => $num,
        'address' => (!isset($address)) ? t('dirección no disponible') : $address,
        'date' => date($short_format[0]),
        'hour' => date($short_format[1] . ' a'),
        'description' => t('Usuario realizo cambio de SIM Card'),
        'state_modal' => t('El cambio de su SIM ha sido exitoso'),
      ];

      // Get vendor config.
      $vendor_info = \Drupal::config('tbo_services.config_vendor_change_sim');

      // Set params for changeSimCard WS.
      $params = [
        'tokens' => [
          'msisdn' => $_SESSION['serviceDetail']['address'],
        ],
        'query' => [
          'typeClient' => 'MSISDN',
          'imsi' => $new_num,
          'idVendor' => $vendor_info->get('id_vendor'),
          'typeVendor' => $vendor_info->get('type_vendor'),
        ],
      ];

      try {
        $response = $this->api->changeSimCard($params);
      }
      catch (\Exception $e) {
        // Admin Audit Log info.
        $log_tokens['error_code'] = $e->getCode();
        $log_tokens['error_message'] = UtilMessage::getMessage($e)['message'];
        $log_tokens['error_roles'] = 'super_admin,tigo_admin';

        // Set audit log.
        $log_tokens['description'] = t('Cambio de SIMCard no exitoso');
        $log_tokens['details'] = t('Usuario @user no pudo cambiar el número de SIMCard del servicio móvil de la línea @phone asociada al contrato @contract.', $details);
        \Drupal::service('tbo_core.audit_log_service')
          ->insertGenericLog($log_tokens);

        // Set fails details to the response.
        $num = $_SESSION['serviceDetail']['address'];
        $fail_tokens = [
          '@oldNum' => $oldValue,
          '@newNum' => $new_num,
          '@line' => '(' . substr($num, 0, 3) . ') ' . substr($num, 3, 3) . '-' . substr($num, 6, 4),
        ];
        $return_data['detail'] = t('No se pudo realizar el cambio de SIM Card:@oldNum por el nuevo número: @newNum de la linea @line', $fail_tokens);
        $return_data['description'] = t('Usuario realizo cambio de SIM Card - error');
        $return_data['state_modal'] = t('El cambio de su SIM no se ha podido realizar');
        $return_data['response'] = UtilMessage::getMessage($e);
        try {
          $tigoId = \Drupal::service('tigoid.repository')
            ->getTigoId(\Drupal::currentUser()->id());
          $this->segment->track([
            'event' => 'TBO - Cambio de Simcard - Tx',
            'userId' => $tigoId,
            'properties' => [
              'category' => 'Portafolio de Servicios',
              'label' => 'Móvil - Fallido - movil',
              'site' => 'NEW',
            ],
          ]);
        }
        catch (\Exception $e) {
          $sms = $e->getMessage();
        }

        return new ResourceResponse($return_data);
      }

      // Send email and sms.
      $tokens = [
        'username' => (empty($full_name)) ? $this->currentUser->getAccountName() : $full_name,
        'service_id' => $_SESSION['serviceDetail']['productId'],
        'line_number' => $num,
        'contract_id' => $_SESSION['serviceDetail']['contractId'],
        'enterprise_name' => $_SESSION['company']['name'],
        'enterprise_type' => $_SESSION['company']['docType'],
        'enterprise_number' => $_SESSION['company']['nit'],
        'old_sim' => $oldValue,
        'new_sim' => $new_num,
        'mail_to_send' => $mail,
        'phone_to_send' => $account->get('phone_number')->value,
      ];

      // Send mail.
      $this->sendMessage->send_message($tokens, 'change_sim_card');

      // Send sms.
      $this->sendMessage->send_sms('change_sim_card', $tokens);

      // Set audit log.
      \Drupal::service('tbo_core.audit_log_service')
        ->insertGenericLog($log_tokens);

      $description_tokens = [
        '@user' => (empty($full_name)) ? $this->currentUser->getAccountName() : $full_name,
        '@oldSim' => $oldValue,
        '@newSim' => $new_num,
        '@line' => '(' . substr($num, 0, 3) . ') ' . substr($num, 3, 3) . '-' . substr($num, 6, 4),
      ];

      // Set additional response values.
      $return_data['detail'] = t('El usuario @user, realizó el cambio de SIM Card: @oldSim por el nuevo número @newSim de la línea @line.', $description_tokens);
      $return_data['response'] = json_decode(json_encode($response), TRUE);
      try {
        $tigoId = \Drupal::service('tigoid.repository')
          ->getTigoId(\Drupal::currentUser()->id());
        $this->segment->track([
          'event' => 'TBO - Cambio de Simcard - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => 'Móvil - Exitoso - movil',
            'site' => 'NEW',
          ],
        ]);
      }
      catch (\Exception $e) {
        $ms = $e->getMessage();
      }
      return new ResourceResponse($return_data);
    }
    else {
      return new ResourceResponse();
    }
  }

  /**
   * Get old value sim.
   *
   * @param string $msisdn
   *   New sim value.
   * @param int $init
   *   Service page.
   * @param int $limit
   *   Limit of results.
   *
   * @return string
   *   Old sim value.
   */
  private function getOldSim($msisdn, $init = 1, $limit = 100) {
    $oldSim = "";
    // Get client data.
    $document_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    $params['query'] = [
      'idType' => $document_type,
      'id' => $document_number,
      'businessUnit' => 'B2B',
      'offset' => $init,
      'limit' => $limit,

    ];
    try {
      $data = $this->api->GetLineDetailsbyDocumentId($params);
    }
    catch (\Exception $e) {
      return $oldSim;
    }
    if (isset($data->lineCollection)) {
      foreach ($data->lineCollection as $line) {
        if ($line->msisdn == $msisdn) {
          $oldSim = $line->imsi;
          break;
        }
      }
      if ($oldSim == "") {
        $this->getOldSim($msisdn, $init + $limit + 1);
      }

    }
    return $oldSim;
  }

}
