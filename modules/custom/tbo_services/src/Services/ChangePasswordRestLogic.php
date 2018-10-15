<?php

namespace Drupal\tbo_services\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_mail\SendMessageInterface;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 */
class ChangePasswordRestLogic {

  protected $api;

  protected $send;

  protected $current_user;

  protected $segment;

  /**
   * ChangePasswordRestLogic constructor.
   */
  public function __construct(SendMessageInterface $sendMessage, AccountInterface $current_user) {
    $this->api = \Drupal::service('tbo_api.client');
    $this->send = $sendMessage;
    $this->currentUser = $current_user;
    \Drupal::service('adf_segment')->segmentPhpInit();
    $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
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
   * @param $current_user
   * @param $data
   */
  public function post($data) {

    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    if (isset($data)) {

      $data_required = [
        'contract_id' => $data['contractId'],
        'new_password' => $data['password'],
        'product_id' => $data['productId'],
        'subscription_number' => $_SESSION['serviceDetail']['subscriptionNumber'],
      ];

      // Params for GetByAccountUsingCustomer.
      $params_customer['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'productId' => $data_required['product_id'],
        'subscription' => $data_required['subscription_number'],
      ];

      $address = $_SESSION['serviceDetail']['address'];
      $user_change = $service->getName();

      $token_log = [
        '@user' => $service->getName(),
        '@serviceId' => $data['productId'],
        '@address' => $address,
        '@contractId' => $data['contractId'],
      ];

      // Save audit log on fail.
      $data_log_fail = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de contraseña wifi no exitoso'),
        'details' => t('Usuario @user no pudo cambiar la contraseña WiFi del servicio fijo con id @serviceId de la @address asociada al contrato @contractId.', $token_log),
        'old_value' => t('No aplica'),
        'new_value' => t('No aplica'),
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
        // Save audit log.
        $data_log_fail['error_code'] = $e->getCode();
        $data_log_fail['error_message'] = UtilMessage::getMessage($e)['message'];
        $data_log_fail['error_roles'] = 'super_admin,tigo_admin';
        $service->insertGenericLog($data_log_fail);
        drupal_set_message(t("Ha ocurrido un error. <br />En estos momentos no podemos procesar su solicitud de cambio de contraseña de su red WiFi"), 'status');
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      // Params for updateWifiPassword.
      $params['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'deviceId' => $serial_number,
      ];

      $body = [
        'suffix' => $suffix,
        'security' => "wpa2",
        'password' => $data_required['new_password'],
        'mac' => $mac,
      ];
      $params['body'] = json_encode($body);

      try {
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        $response = $this->api->updateWifiPassword($params);
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        unset($params);
      }
      catch (\Exception $e) {
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');

        $tigoId = \Drupal::service('tigoid.repository')
          ->getTigoId(\Drupal::currentUser()->id());
        $this->segment->track([
          'event' => 'TBO - Cambio contraseña Wifi - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => 'Internet - Fallido - fijo',
            'site' => 'NEW',
          ],
        ]);

        // Save audit log.
        $data_log_fail['error_code'] = $e->getCode();
        $data_log_fail['error_message'] = UtilMessage::getMessage($e)['message'];
        $data_log_fail['error_roles'] = 'super_admin,tigo_admin';
        $service->insertGenericLog($data_log_fail);

        drupal_set_message(t("Ha ocurrido un error. <br />En estos momentos no podemos procesar su solicitud de cambio de contraseña de su red WiFi"), 'error');

        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      // Save audit log on success.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de contraseña wifi exitoso'),
        'details' => t('Usuario @user cambió la contraseña WiFi del servicio fijo con id @serviceId de la dirección asociada al contrato @contractId.', $token_log),
        'old_value' => t('No aplica'),
        'new_value' => t('No aplica'),
      ];
      // Save audit log.
      $service->insertGenericLog($data_log);
      $tokens = [
        'user_change' => $user_change,
        'address' => $address,
        'service_id' => $_SESSION['serviceDetail']['productId'],
        'contract_id' => isset($data['contractId']) ? $data['contractId'] : '',
        'enterprise' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'enterprise_num' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'enterprise_doc' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
      ];

      $users = \Drupal::service('tbo_services.tbo_services_repository')
        ->getAllTigoAdmins();
      // Send message.
      foreach ($users as $key => $value) {
        $tokens['username'] = (isset($value->full_name) && !empty($value->full_name)) ? $value->full_name : $value->name;
        $tokens['mail_to_send'] = $value->mail;
        $this->send->send_message($tokens, 'change_wifi_pass');

        if (!empty($value->phone_number)) {
          $tokens['phone_to_send'] = $value->phone_number;
          $this->send->send_sms('change_wifi_pass', $tokens, $exception = FALSE);
        }
      }

      $response = json_decode(json_encode($response), TRUE);
      drupal_set_message(t("Proceso exitoso. <br />Se ha cambiado correctamente la contraseña de su red WiFi"), 'status');

      $tigoId = \Drupal::service('tigoid.repository')
        ->getTigoId(\Drupal::currentUser()->id());
      $this->segment->track([
        'event' => 'TBO - Cambio contraseña Wifi - Tx',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Portafolio de Servicios',
          'label' => 'Internet - Exitoso - fijo',
          'site' => 'NEW',
        ],
      ]);

      return new ResourceResponse($response);
    }
    else {
      return [];
    }
  }

}
