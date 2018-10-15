<?php

namespace Drupal\tbo_services\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_mail\SendMessageInterface;
use Drupal\adf_core\Util\UtilMessage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_segment\Services\AdfSegmentService;

/**
 * Class ChangeWifiChannelRestLogic.
 *
 * @package Drupal\tbo_services\Services
 */
class ChangeWifiChannelRestLogic {

  protected $api;

  protected $send;

  protected $currentUser;

  protected $segment;

  /**
   * ChangeWifiChannelRestLogic constructor.
   *
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Messaging service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current User info Object.
   * @param \Drupal\adf_segment\Services\AdfSegmentService $segment
   *   Service for creating Segment tracks.
   */
  public function __construct(SendMessageInterface $sendMessage, AccountInterface $current_user, AdfSegmentService $segment) {
    $this->api = \Drupal::service('tbo_api.client');
    $this->send = $sendMessage;
    $this->currentUser = $current_user;
    $segment->segmentPhpInit();
    $this->segment = $segment->getSegmentPhp();
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // The first time we load the block, the call will be through GET,
    // so we use the Session info.
    $response = $_SESSION['serviceDetail'];
    return new ResourceResponse($response);
  }

  /**
   * Post.
   *
   * @param array $data
   *   Data for the service call.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Resource Response object.
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
        'channel' => $data['channel'],
        'product_id' => $data['productId'],
        'subscription_number' => $data['subscriptionNumber'],
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
      $detailsOnFail = '';
      $roles = \Drupal::currentUser()->getRoles(TRUE);
      if (in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
        $detailsOnFail = 'Usuario @user no pudo cambiar el canal de su red WiFi del servicio fijo con id @serviceId de la @address asociada al contrato @contractId. El error retornado por el servicio web a consumir fué @error_code y descripción: @error_description';
      }
      else {
        $detailsOnFail = 'Usuario @user no pudo cambiar el canal de su red WiFi del servicio fijo con id @serviceId de la @address asociada al contrato @contractId.';
      }

      // Save audit log on fail.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Cambio de canal de red WiFi no exitoso'),
        'old_value' => t('No aplica'),
        'new_value' => t('No aplica'),
      ];
      $serial_number = '';
      $suffix = '';
      $mac = '';

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
      }
      catch (\Exception $e) {
        // Save audit log.
        $errorInfo = UtilMessage::getMessage($e);

        if (in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
          $token_log['@error_code'] = $errorInfo['code'];
          $token_log['@error_description'] = $errorInfo['message'];
        }

        $data_log['details'] = t($detailsOnFail, $token_log);
        $service->insertGenericLog($data_log);
        drupal_set_message(t("Ha ocurrido un error. <br />En estos momentos no podemos procesar su solicitud de cambio de canal de su red WiFi"), 'error');

        return new ResourceResponse($errorInfo);
      }

      // Params for changeWifiChannel.
      $params['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'deviceId' => $serial_number,
      ];

      $body = [
        'suffix' => $suffix,
        'channel' => $data_required['channel'],
        'mac' => $mac,
      ];
      $params['body'] = json_encode($body);

      try {
        $responseChange = $this->api->changeWifiChannel($params);
        unset($params);
      }
      catch (\Exception $e) {
        // Save audit log.
        $errorInfo = UtilMessage::getMessage($e);
        if (in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
          $token_log['@error_code'] = $errorInfo['code'];
          $token_log['@error_description'] = $errorInfo['message'];
        }

        $data_log['details'] = t($detailsOnFail, $token_log);
        $service->insertGenericLog($data_log);
        drupal_set_message(t("Ha ocurrido un error. <br />En estos momentos no podemos procesar su solicitud de cambio de canal de su red WiFi"), 'error');

        // Create the segment track.
        $this->segment->track([
          'event' => t('TBO - Cambiar canal de red wifi - Tx'),
          'userId' => $tigoId,
          'properties' => [
            'category' => t('Portafolio de Servicios'),
            'label' => t("Internet - Fallido - fijo"),
            'site' => 'NEW',
          ],
        ]);

        return new ResourceResponse($errorInfo);
      }

      // Save audit log on success.
      $token_log['@new_channel'] = $data_required['channel'];
      $data_log['description'] = t('Cambio de canal de la red WiFi exitoso');
      $data_log['new_value'] = $data_required['channel'];
      $data_log['details'] = t('Usuario @user cambió el canal de la red WiFi del servicio fijo con id @serviceId de la @address asociada al contrato @contractId. El nuevo canal es @new_channel', $token_log);
      $service->insertGenericLog($data_log);

      // Send email notification.
      $tokens = [
        'user_change' => $user_change,
        'address' => $address,
        'service_id' => $_SESSION['serviceDetail']['productId'],
        'contract_id' => isset($data['contractId']) ? $data['contractId'] : '',
        'enterprise' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'enterprise_num' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'enterprise_doc' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
        'new_channel' => $data_required['channel'],
      ];

      $users = \Drupal::service('tbo_services.tbo_services_repository')
        ->getAllTigoAdmins();
      foreach ($users as $key => $value) {
        $tokens['username'] = (isset($value->full_name) && !empty($value->full_name)) ? $value->full_name : $value->name;

        $tokens['mail_to_send'] = $value->mail;
        $this->send->send_message($tokens, 'change_wifi_channel');

        if (!empty($value->phone_number)) {
          $tokens['phone_to_send'] = $value->phone_number;
          $this->send->send_sms('change_wifi_channel', $tokens, $exception = FALSE);
        }
      }

      $response = json_decode(json_encode($responseChange), TRUE);
      drupal_set_message(t("Proceso exitoso. <br />Se ha cambiado correctamente el canal de su red WiFi"), 'status');

      // Create the segment track.
      $this->segment->track([
        'event' => t('TBO - Cambiar canal de red wifi - Tx'),
        'userId' => $tigoId,
        'properties' => [
          'category' => t('Portafolio de Servicios'),
          'label' => t("Internet - Exitoso - fijo"),
          'site' => 'NEW',
        ],
      ]);

      return new ResourceResponse($response);
    }

    return new ResourceResponse();
  }

}
