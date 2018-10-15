<?php

namespace Drupal\tbo_services\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_mail\SendMessageInterface;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ChangeWifiNetNameRestLogic.
 *
 * @package Drupal\tbo_services
 */
class ChangeWifiNetNameRestLogic {

  protected $api;
  protected $send;
  protected $current_user;
  protected $segment;

  /**
   * ChangeWifiNetNameRestLogic constructor.
   *
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Message.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(SendMessageInterface $sendMessage, AccountInterface $current_user) {
    $this->api = \Drupal::service('tbo_api.client');
    $this->send = $sendMessage;
    $this->currentUser = $current_user;
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();
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
   * {@inheritdoc}
   */
  public function post($data) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    if (isset($data)) {
      $message_success = t("Proceso exitoso. <br />Se ha cambiado correctamente el nombre de su red WiFi");
      $message_error = t("Ha ocurrido un error.<br>En este momento no podemos procesar su solicitud de cambio de nombre de su red WiFi, por favor intente más tarde.");

      $data_required = [
        'contract_id' => $data['contractId'],
        'SSID' => $data['SSID'],
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
        'description' => t('Cambio de nombre de red wifi no exitoso'),
        'details' => t('Usuario @user no pudo cambiar el nombre de la red WiFi del servicio fijo con id @serviceId de la @address asociada al contrato @contractId.', $token_log),
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
        // Send segment track.
        $this->sendSegmentTrack('Fallido');

        // Save audit log.
        $service->insertGenericLog($data_log_fail);
        drupal_set_message($message_error, 'error');
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      // Params for update WifiNetName.
      $params['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'deviceId' => $serial_number,
      ];

      $body = [
        'suffix' => $suffix,
        'ssid' => $data_required['SSID'],
        'mac' => $mac,
      ];
      $params['body'] = json_encode($body);

      try {
        $response = $this->api->updateWifiNetName($params);
        unset($params);
      }
      catch (\Exception $e) {
        // Send segment track.
        $this->sendSegmentTrack('Fallido');

        // Save audit log.
        $service->insertGenericLog($data_log_fail);
        drupal_set_message($message_error, 'error');
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      // Send segment track.
      $this->sendSegmentTrack('Exitoso');

      // Save audit log on success.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de nombre de la red WiFi exitoso'),
        'details' => t('Usuario @user cambió el nombre de la red WiFi del servicio fijo con id @serviceId de la dirección @address asociada al contrato @contractId. Donde @serviceId corresponde al id del producto de internet.', $token_log),
        'old_value' => t('No aplica'),
        'new_value' => t('No aplica'),
      ];
      // Save audit log.
      $service->insertGenericLog($data_log);
      $tokens = [
        'user_change' => $user_change,
        'address' => $address,
        'service_id' => $_SESSION['serviceDetail']['productId'],
        'wifi_new_name' => isset($data['SSID']) ? $data['SSID'] : '',
        'contract_id' => isset($data['contractId']) ? $data['contractId'] : '',
        'enterprise' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'enterprise_num' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'enterprise_doc' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
      ];

      $users = \Drupal::service('tbo_services.tbo_services_repository')->getAllTigoAdmins();
      // Send message.
      foreach ($users as $key => $value) {
        $tokens['username'] = (isset($value->full_name) && !empty($value->full_name)) ? $value->full_name : $value->name;
        $tokens['mail_to_send'] = $value->mail;
        $this->send->send_message($tokens, 'change_wifi_net_name');

        if (!empty($value->phone_number)) {
          $tokens['phone_to_send'] = $value->phone_number;
          $this->send->send_sms('change_wifi_net_name', $tokens, $exception = FALSE);
        }
      }

      $response = json_decode(json_encode($response), TRUE);
      drupal_set_message($message_success, 'status');
      $headers = [
        'Content-Type' => 'application/json',
      ];
      return new ModifiedResourceResponse($response, 200, $headers);
    }
    else {
      return [];
    }
  }

  /**
   * Implements sendSegmentTrack().
   *
   * @param $status
   *   The transaction status.
   */
  public function sendSegmentTrack($status) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    if (isset($tigoId)) {
      $category = $_SESSION['serviceDetail']['category'];
      try {
        $segment_track = [
          'event' => 'TBO - Cambiar nombre de red wifi - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => $category . ' - ' . $status . ' - fijo',
            'site' => 'NEW',
          ],
        ];

        $this->segment->track($segment_track);
      }
      catch (\Exception $e) {

      }
    }
  }
}
