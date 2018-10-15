<?php

namespace Drupal\tbo_services\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_mail\SendMessageInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * ChangeSecurityTypeRestLogic manage the rest logic.
 */
class ChangeSecurityTypeRestLogic {

  protected $api;

  protected $send;

  protected $currentUser;

  protected $segmentService;

  protected $segment;

  /**
   * ChangeSecurityTypeRestLogic constructor.
   *
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Send Message.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user info.
   */
  public function __construct(SendMessageInterface $sendMessage, AccountInterface $current_user) {
    $this->api = \Drupal::service('tbo_api.client');
    $this->send = $sendMessage;
    $this->currentUser = $current_user;
    $this->segmentService = \Drupal::service('adf_segment');
    $this->segmentService->segmentPhpInit();
    $this->segment = $this->segmentService->getSegmentPhp();
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $response = $_SESSION['serviceDetail'];
    return new ResourceResponse($response);
  }

  /**
   * Post method.
   *
   * @param array $data
   *   Parameters for the request.
   *
   * @return array|ResourceResponse
   *   Resource Response.
   */
  public function post(array $data = []) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $tigoId = \Drupal::service('tigoid.repository')
      ->getTigoId($this->currentUser->id());

    if (isset($data)) {
      $data_required = [
        'contract_id' => $data['contractId'],
        'password' => $data['new_password'],
        'security_type' => $data['security_type'],
        'product_id' => $data['productId'],
        'subscription_number' => $_SESSION['serviceDetail']['subscriptionNumber'],
      ];

      // Params for GetByAccountUsingCustomer.
      $params_customer['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'productId' => $data_required['product_id'],
        'subscription' => $data_required['subscription_number'],
      ];

      $serial_number = '';
      $suffix = '';
      $mac = '';

      // Audit log info on error.
      $address = $_SESSION['serviceDetail']['address'];
      $user_change = $service->getName();
      $token_log = [
        '@user' => $service->getName(),
        '@serviceId' => $data['productId'],
        '@address' => $address,
        '@contractId' => $data['contractId'],
      ];
      $dataLogError = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de tipo de seguridad wifi no exitoso'),
        'details' => t('Usuario @user no pudo cambiar el tipo de seguridad de su red WiFi del servicio fijo con id @serviceId de la dirección @address asociada al contrato @contractId.', $token_log),
        'old_value' => t('No disponible'),
        'new_value' => $data['security_type'],
      ];

      try {
        $result = $this->api->getByAccountUsingContract($params_customer);
        if (!empty($result)) {
          // Property added dynamically, see getByAccountUsingContract!
          $suffix = $result->mediaTypeSuffix;
          $devices = $result->devices;
          $device = $devices[0];
          $serial_number = $device->serialNumber;
          $mac = $device->extendedUniqueIdentifier;
        }
        unset($result);
        unset($params_customer);

      }
      catch (\Exception $e) {
        // Save ERROR audit log.
        $service->insertGenericLog($dataLogError);
        drupal_set_message(t("Ha ocurrido un error. <br />En estos momentos no podemos procesar su solicitud de cambio de tipo de seguridad de su red WiFi"), 'status');

        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      // Params for changeSecurityType.
      $params['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'deviceId' => $serial_number,
      ];

      $body = [
        'suffix' => $suffix,
        'security' => $data_required['security_type'],
        'password' => $data_required['password'],
        'mac' => $mac,
      ];
      $params['body'] = json_encode($body);

      try {
        $response = $this->api->changeWifiSecurityType($params);
        unset($params);
      }
      catch (\Exception $e) {
        // Save audit log.
        $service->insertGenericLog($dataLogError);
        drupal_set_message(t("Ha ocurrido un error. <br />En estos momentos no podemos procesar su solicitud de cambio de tipo de seguridad de su red WiFi"), 'error');

        // Create track for Segment.
        $this->segment->track([
          'event' => t('TBO - Cambiar seguridad wifi - Tx'),
          'userId' => $tigoId,
          'properties' => [
            'category' => t('Portafolio de Servicios'),
            'label' => t('Internet - Fallido - fijo'),
          ],
        ]);

        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      // Save audit log on success.
      $token_log['@new_security_type'] = $data['security_type'];
      $dataLogSuccess = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de tipo de seguridad wifi exitoso'),
        'details' => t('Usuario @user cambió el tipo de seguridad de su red WiFi a @new_security_type del servicio fijo con id @serviceId de la dirección @address asociada al contrato @contractId.', $token_log),
        'old_value' => t('No aplica'),
        'new_value' => $data['security_type'],
      ];
      $service->insertGenericLog($dataLogSuccess);

      // Send email and SMS notifications to users.
      $tokensNotification = [
        'user_change' => $user_change,
        'address' => $address,
        'service_id' => $_SESSION['serviceDetail']['productId'],
        'contract_id' => isset($data['contractId']) ? $data['contractId'] : '',
        'enterprise' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'enterprise_num' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'enterprise_doc' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
        'new_security_type' => $data['security_type'],
      ];
      $users = \Drupal::service('tbo_services.tbo_services_repository')
        ->getAllTigoAdmins();
      foreach ($users as $key => $value) {
        $tokensNotification['username'] = (isset($value->full_name) && !empty($value->full_name)) ? $value->full_name : $value->name;
        $tokensNotification['mail_to_send'] = $value->mail;
        $this->send->send_message($tokensNotification, 'change_security_type');

        if (!empty($value->phone_number)) {
          $tokensNotification['phone_to_send'] = $value->phone_number;
          $this->send->send_sms('change_security_type', $tokensNotification, $exception = FALSE);
        }
      }

      $response = json_decode(json_encode($response), TRUE);
      drupal_set_message(t("Proceso exitoso. <br />Se ha cambiado correctamente el tipo de seguridad de su red WiFi"), 'status');

      // Create track for Segment.
      $this->segment->track([
        'event' => t('TBO - Cambiar seguridad wifi - Tx'),
        'userId' => $tigoId,
        'properties' => [
          'category' => t('Portafolio de Servicios'),
          'label' => t('Internet - Exitoso - fijo'),
        ],
      ]);

      return new ResourceResponse($response);
    }

    return new ResourceResponse([FALSE]);
  }

}
