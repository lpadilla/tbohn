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
 * Class ChangeWifiDmzRestLogic.
 *
 * @package Drupal\tbo_services
 */
class ChangeWifiDmzRestLogic {
  protected $api;
  protected $send;
  protected $current_user;
  protected $segment;

  /**
   * ChangeWifiDmzRestLogic constructor.
   *
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Interfaz de mensajes.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Usuario actual.
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
   * Implement post function.
   *
   * @param array|object $data
   *   Informacion de la solicitud.
   *
   * @return array|\Drupal\rest\ModifiedResourceResponse|\Drupal\rest\ResourceResponse
   *   Resultado de la solicitud.
   */
  public function post($data) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    if (isset($data)) {
      $message_success =t('Proceso exitoso. <br/>Se ha cambiado correctamente el dmz de su red WiFi.');
      $message_error = t('Ha ocurrido un error.<br>En este momento no podemos procesar su solicitud de configuración de la DMZ de la red WiFi, por favor intente más tarde.');

      $data_required = [
        'contract_id' => $data['contractId'],
        'ipdmz' => $data['ipdmz'],
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
        '@ipdmz' => $data['ipdmz'],
      ];

      // Save audit log on fail.
      $data_log_fail = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Configuración DMZ de la red WiFi no exitoso'),
        'details' => t('Usuario @user no pudo configurar la DMZ de la red WiFi del servicio fijo con id @serviceId de la dirección @address asociada al contrato @contractId.', $token_log),
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
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

        $error = UtilMessage::getMessage($e);
        $error['message_error'] = $message_error;
        return new ResourceResponse($error);
      }

      // Params for update WifiNetName.
      $params['tokens'] = [
        'contractId' => $data_required['contract_id'],
        'deviceId' => $serial_number,
      ];

      $body = [
        'suffix' => $suffix,
        'ipdmz' => $data_required['ipdmz'],
        'mac' => $mac,
      ];
      $params['body'] = json_encode($body);

      $roles = $this->currentUser->getRoles();

      try {
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        $response = $this->api->updateWifiDmz($params);
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        unset($params);
      }
      catch (\Exception $e) {
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        $error = UtilMessage::getMessage($e);

        if (is_array($roles) && (in_array('super_admin', $roles) || in_array('tigo_admin', $roles))) {
          $token_log['@code_error'] = $error['code'];
          $token_log['@message_error'] = $error['message_error'];
          $data_log_fail['details'] = t('Usuario @user no pudo configurar la DMZ de la red WiFi del servicio fijo con id @serviceId de la dirección @address asociada al contrato @contractId.  El error retornado por el servicio web a consumir fue @code_error y descripción "@message_error."', $token_log);
        }

        // Send segment track.
        $this->sendSegmentTrack('Fallido');

        // Save audit log.
        $service->insertGenericLog($data_log_fail);

        $error['message_error'] = $message_error;
        return new ResourceResponse($error);
      }

      // Send segment track.
      $this->sendSegmentTrack('Exitoso');

      // Save audit log on success.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Servicios'),
        'description' => t('Configuración DMZ de la red WiFi exitoso'),
        'details' => t('Usuario @user configuro DMZ de la red WiFi del servicio fijo con id @serviceId de la dirección @address asociada al contrato @contractId. La IP configurada es @ipdmz', $token_log),
        'old_value' => t('No aplica'),
        'new_value' => $data['ipdmz'],
      ];
      // Save audit log.
      $service->insertGenericLog($data_log);
      $tokens = [
        'username' => $service->getName(),
        'user_change' => $user_change,
        'address' => $address,
        'wifi_dmz' => $data['ipdmz'],
        'service_id' => $_SESSION['serviceDetail']['productId'],
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

        try {
          $this->send->send_message($tokens, 'change_wifi_dmz');
        }
        catch (\Exception $e) {
        }

        if (!empty($value->phone_number)) {
          try {
            $tokens['phone_to_send'] = $value->phone_number;
            $this->send->send_sms('change_wifi_dmz', $tokens, $exception = FALSE);
          }
          catch (\Exception $e) {
          }
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
   * @param string $status
   *   The transaction status.
   */
  public function sendSegmentTrack($status) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    if (isset($tigoId)) {
      try {
        $segment_track = [
          'event' => 'TBO - Configurar DMZ - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => $_SESSION['serviceDetail']['category'] . ' - ' . $status . ' - fijo',
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
