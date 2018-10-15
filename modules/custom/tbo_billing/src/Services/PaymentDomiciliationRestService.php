<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\rest\ResourceResponse;

/**
 * Class ServicePortfolioService.
 *
 * @package Drupal\tbo_billing\Services
 */
class PaymentDomiciliationRestService implements PaymentDomiciliationRestServiceInterface {

  protected $api;
  protected $currentUser;
  protected $domiciliationService;
  protected $log;
  protected $tbo_config;
  protected $segment;
  /**
   * $service_message => Get instance service email.
   */
  protected $service_message;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   * @param PaymentDomiciliationService $domiciliationService
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AuditLogService $log, PaymentDomiciliationService $domiciliationService) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->log = $log;
    $this->domiciliationService = $domiciliationService;
    $this->service_message = \Drupal::service('tbo_mail.send');
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $response = [];

    // Validate delete card.
    if (isset($_GET['type']) && $_GET['type'] == 'deleteCard') {
      $delete_response = $delete_fixed = FALSE;
      $recurring = TRUE;
      $method = 'deleteCreditToken';
      $method_delete_get_card = 'getCardToken';
      $method_delete_recurring = 'recurringInfoByContractId';
      $method_delete_recurring_with_target = 'deleteRecurringBillingInfo';
      $uid = \Drupal::currentUser();
      $clientId = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
      $docType = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';

      // Save Audit log.
      $this->log->loadName();
      $name = $this->log->getName();

      $this->saveAuditLog('Usuario solicita la eliminación de una tarjeta de crédito',
        'Usuario ' . $name . ' solicita la eliminación de una tarjeta de crédito');

      // Get data request.
      $card = $_GET['card'];
      $brand = $_GET['name'];
      $cardInfo = substr($card, -4);
      $card_number = $_GET['number'];

      $recurring_info = $_SESSION['recurring_info_payment'];
      if ($recurring_info && $cardInfo == $recurring_info->cardInfo) {
        // Delete program payment
        // Parameters for service.
        if ($_SESSION['environment'] == 'fijo') {
          $params['query'] = [
            'limit' => 4,
          ];

          $params['tokens'] = [
            'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
          ];

          $params_body = [
            'cardToken' => $_GET['number'],
            'customerId' => $clientId,
            'documentType' => $docType,
            'transactionId' => rand(),
            'customerIpAddress' => \Drupal::request()->getClientIp(),
          ];

          $params['body'] = json_encode($params_body);
        }
        elseif ($_SESSION['environment'] == 'movil') {
          $params = [
            'headers' => [
              'Content-Type' => 'application/json',
              'transactionId' => substr(md5($this->domiciliationService->getTransactionId()), 0, 16),
              'platformId' => 12347,
            ],
            'tokens' => [
              'clientId' => isset($_SESSION['sendDetail']['docNumber']) ? $_SESSION['sendDetail']['docNumber'] : '',
              'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
            ],
          ];
          $method_delete_recurring_with_target = 'deleteBillingAccount';
          $method_delete_recurring = 'getBillingAccount';
        }

        // Delete RecurringBillingInfo.
        try {
          $delete_response = $this->api->$method_delete_recurring_with_target($params);
        }
        catch (\Exception $e) {
          // Send segment track.
          $this->sendSegmentTrack($_SESSION['environment'], 'Fallido');

          $mensaje = UtilMessage::getMessage($e);
          // Return message in rest.
          drupal_set_message($mensaje['message'], 'error');
          $requestUrl = \Drupal::request()->server->get('HTTP_REFERER');
          return new ResourceResponse($mensaje['message']);
          // Return new RedirectResponse($requestUrl);
        }

        // Delete cache service recurringInfoByContractId.
        $params = [
          'tokens' => [
            'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
          ],
          'query' => [
            'limit' => 4,
          ],
        ];

        if ($method_delete_recurring == 'getBillingAccount') {
          $params = [
            'tokens' => [
              'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
            ],
            'query' => [],
          ];
        }

        BaseApiCache::delete('service', $method_delete_recurring, array_merge($params['tokens'], $params['query']));
      }
      else {
        $recurring = FALSE;
      }

      if (!$recurring || $delete_response) {
        if ($_SESSION['environment'] == 'fijo') {
          // Parameters for service.
          $params['tokens'] = [
            'clientId' => $clientId,
            'docType' => $docType,
          ];

          $params_body = [
            'cardToken' => $card_number,
            'transactionId' => rand(),
            'customerIpAddress' => \Drupal::request()->getClientIp(),
          ];

          $params['body'] = json_encode($params_body);
        }
        elseif ($_SESSION['environment'] == 'movil') {
          $params = [
            "dni" => $clientId,
            'tokens' => [
              [
                "correlationId" => $card_number,
              ],
            ],
          ];

          $jsonBody = json_encode($params);

          $params = [
            'headers' => [
              'Content-Type' => 'application/json',
              'transactionId' => substr(md5($this->domiciliationService->getTransactionId()), 0, 16),
              'platformId' => 12347,
            ],
            'body' => $jsonBody,
          ];

          $method = 'deleteCreditCards';
          $method_delete_get_card = 'getCreditsCardByIdentification';
        }

        try {
          $response = $this->api->$method($params);
        }
        catch (\Exception $e) {
          // Send segment track.
          $this->sendSegmentTrack($_SESSION['environment'], 'Fallido');

          $mensaje = UtilMessage::getMessage($e);

          if ($_SESSION['environment'] == 'fijo') {
            if (strtoupper($mensaje['message_error']) == strtoupper('Operacion no exitosa.')) {
              $delete_fixed = TRUE;
            }
          }

          if (!$delete_fixed) {
            // Return message in rest.
            drupal_set_message($mensaje['message'], 'error');
            // Save audit log.
            $this->saveAuditLog('Usuario no eliminó nueva tarjeta de crédito',
              'Usuario ' . $name . ' no pudo eliminiar la tarjeta de crédito. Error retornado por el servicio web a consumir fue ' . $e->getCode() . ' y descripción ' . $mensaje['message']);
            return new ResourceResponse($mensaje['message']);
            // Return new RedirectResponse($requestUrl);
          }
        }

        if ($response || $delete_fixed) {
          // Send segment track.
          $this->sendSegmentTrack($_SESSION['environment'], 'Exitoso');

          // Save audit log.
          $this->saveAuditLog('Usuario eliminó tarjeta de crédito',
            'Usuario ' . $name . ' eliminó la tarjeta ' . $brand . '**** **** **** ' . $cardInfo);
          /**
           * envio de correo de notificacion de desprogramacion de pago programado
           */
          $document_number = $clientId;
          $document_type = $docType;
          $enterprise_name = $_SESSION['company']['name'];
          // 600006858393.
          $contractId = $_SESSION['sendDetail']['contractId'];

          $mail = $uid->getEmail();
          /*if (isset($_GET['mail'])) {
          $mail = $_GET['mail'];
          }*/

          \Drupal::service('tbo_billing.payment_domiciliation')
            ->sendEmail($this->service_message, 'remove_card_token', $name, $mail, \Drupal::currentUser()
              ->id(), $enterprise_name, $document_number, $document_type, $contractId, $cardInfo, strtoupper($_GET['type_card']));

          // Set message.
          drupal_set_message(t('Proceso exitoso. <br /> Se ha eliminado correctamente la tarjeta de crédito @brand **** **** **** @cardInfo y los pagos programados asociados a esta”.', ['@brand' => $brand, '@cardInfo' => $card]));

          // Remove cache
          // params $category, $key, $arguments
          // Parameters for service getCardToken.
          $params = [
            'tokens' => [
              'docType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
              'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
            ],
            'query' => [],
          ];

          if ($method_delete_get_card == 'getCreditsCardByIdentification') {
            $params['tokens'] = [
              'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
            ];
          }

          BaseApiCache::delete('service', $method_delete_get_card, array_merge($params['tokens'], $params['query']));

          return new ResourceResponse(['message' => 'Sucess']);
        }
        else {
          // Send segment track.
          $this->sendSegmentTrack($_SESSION['environment'], 'Fallido');

          $code = '';
          $error = '';

          $this->saveAuditLog('Usuario no eliminó nueva tarjeta de crédito',
            'Usuario ' . $name . ' no pudo eliminiar la tarjeta de crédito. Error retornado por el servicio web a consumir fue ' . $code . ' y descripción ' . $error);
          drupal_set_message(t('Ha ocurrido un error. <br /> En este momento no puede eliminarse la tarjeta, por favor intente de nuevo más tarde '), 'error');
          return new ResourceResponse(['message' => 'Error']);
        }
      }
    }
    else {
      if (isset($_SESSION['company'])) {

        $block = \Drupal::request()->get('block');
        $cid = 'config:block:' . $block;
        $block = \Drupal::cache()->get($cid);

        if (!$block) {
          $error = [
            'error_code' => "700",
            'error_message' => "No se pudo obtener la configuración del widget",
          ];
          $response = new ResourceResponse($error, 500);
          return $response;
        }

        try {
          $service = \Drupal::service('tbo_billing.payment_domiciliation');
          $info = $service->getRecurringInfoByContractId($block->data['card_tokens']);
        }
        catch (\Exception $e) {
          // Return message in rest.
          return new ResourceResponse(UtilMessage::getMessage($e));
        }

        $data = $block->data['actions'];

        foreach ($data as $k => $button) {
          $data[$k] = 0;
          if (isset($info->buttons) && in_array($k, $info->buttons)) {
            $data[$k] = 1;
          }
        }

        $data['show_description_block_configured_payment'] = 0;
        $data['show_description_block_payment_not_configured'] = 0;
        $data['show_description_block_payment_method_debit'] = 0;
        foreach ($data as $key => $value) {
          if ($key === $info->message) {
            $data[$key] = 1;
          }
        }

        /**
         * Si $info trae data_recurring_payment, tambien hay que enviarlo a la plantilla
         * para mostrar los datos del pago recurrente
         */

        if (isset($info->data_recurring_payment)) {

          $fields = $block->data['table_fields'];
          uasort($fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

          foreach ($fields as $key_field => $field) {
            if ($field['show'] == 1) {
              $data['fields'][$key_field]['label'] = isset($field['label']) ? $field['label'] : $field['title'];
              $classes = ["field-" . $field['service_field'], $field['class']];
              $data['fields'][$key_field]['class'] = implode(" ", $classes);
              $data['fields'][$key_field]['service_field'] = $field['service_field'];
              unset($classes);
            }
            else {
              unset($fields[$key_field]);
            }
          }

          foreach ($info->data_recurring_payment as $key => $value) {
            foreach ($fields as $key_field => $field) {
              $data['fields'][$key_field]['value'] = $value;
            }

            continue;
          }
        }

        return new ResourceResponse($data);
      }
      else {
        return new ResourceResponse("No ha seleccionado una empresa");
      }
    }
  }

  /**
   * Guardado log auditoria.
   */
  public function saveAuditLog($description, $details) {
    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => t('Facturación'),
      'description' => $description,
      'details' => $details,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $this->log->insertGenericLog($data);
  }

  /**
   * @param $environment
   */
  public function sendSegmentTrack($environment, $status) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    if (isset($tigoId)) {
      try {
        $segment_track = [
          'event' => 'TBO - Eliminar Tarjeta - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pago automático',
            'label' => 'Aceptar' . ' - ' . $environment . ' - ' . $status,
            'site' => 'NEW',
          ],
        ];

        $this->segment->track($segment_track);
      }
      catch (\Exception $e) {
        // Send segment exception.
      }
    }
  }

}
