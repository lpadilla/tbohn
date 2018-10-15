<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class SetUpInvoiceDeliveryRestService.
 *
 * @package Drupal\tbo_billing\Services
 */
class SetUpInvoiceDeliveryRestService {
  protected $api;
  protected $currentUser;
  protected $tbo_config;
  protected $segment;

  /**
   * CurrentInvoiceService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
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
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Get data invoice delivery.
    $environment = isset($_SESSION['environment']) ? $_SESSION['environment'] : $_SESSION['company']['environment'];

    $params['tokens'] = [
      'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
    ];

    $params['no_exception'] = TRUE;
    $data = [];

    if ($environment == 'fijo') {
      $label = t('Impresa');
      $billing = 'impresa';
      $address = $_SESSION['sendDetail']['address'];
      $city = $_SESSION['sendDetail']['city'];
      $contractId = $_SESSION['sendDetail']['contractId'];
      $invoiceDetailOption = '';

      try {
        $response = $this->api->paperlessByContractId($params);
      }
      catch (\Exception $e) {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      if ($response) {
        $billing = 'ambas';
        $label = t('Digital / Impresa');

        $data[] = [
          'contractId' => $contractId,
          'show_invoice_billing' => $billing,
          'show_invoice_address' => $address . ' ' . $city,
          'show_invoice_email' => $response->email,
          'show_invoice_city' => $city,
          'invoiceDetailOption' => $invoiceDetailOption,
          'label' => $label,
          'email' => $response->email,
          'address' => $address,
          'city' => $city,
        ];

        return new ResourceResponse($data);
      }

      // Si el servicio no esta activo.
      $data[] = [
        'contractId' => $contractId,
        'show_invoice_billing' => $billing,
        'show_invoice_address' => $address,
        'show_invoice_city' => $city,
        'invoiceDetailOption' => $invoiceDetailOption,
        'label' => $label,
        'email' => '',
        'address' => $address,
        'city' => $city,
      ];

      return new ResourceResponse($data);
    }
    elseif ($environment == 'movil') {
      try {
        $response = $this->api->getPaperlessInvoiceStatusV2($params);
      }
      catch (\Exception $e) {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      if (isset($response)) {
        $billing = '';
        $label = '';
        $email = '';
        $address = '';
        switch ($response[0]->deliveryOption) {
          case 'PRINTED':
            $billing = 'impresa';
            $label = t('Impresa');
            $address = $response[0]->address;
            break;

          case 'ELECTRONIC':
            $billing = 'digital';
            $label = t('Digital');
            $email = $response[0]->address;
            break;

          case 'BOTH':
            $billing = 'ambas';
            $label = t('Digital / Impresa');
            break;
        }

        $city = isset($_SESSION['sendDetail']['city']) ? $_SESSION['sendDetail']['city'] : '';
        if ($billing == 'ambas') {
          // Si es ambas el valor devuelto por tigo es ejemplo: "email:dgamba@sperling.com","address:CL 17 N 68 61, ZONA IND MONTEVIDEO".
          $address_explode = explode(';', $response[0]->address);
          foreach ($address_explode as $value) {
            $string[] = explode(':', $value);
          }

          $email = $string[0][1];
          $address = $string[1][1];
        }

        $data[] = [
          'contractId' => $response[0]->contractId,
          'show_invoice_billing' => $billing,
          'show_invoice_address' => $address . ' ' . $city,
          'show_invoice_email' => $email,
          'show_invoice_city' => $city,
          'invoiceDetailOption' => $response[0]->invoiceDetailOption,
          'label' => $label,
          'email' => $email,
          'address' => $address,
          'city' => $city,
        ];

        return new ResourceResponse($data);
      }
    }

    if ($data) {
      return new ResourceResponse($data);
    }

    return new ResourceResponse('error obteniendo los datos');
  }

  /**
   * Responds to POST requests.
   * calls create method.
   *
   * @param $params
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $params) {
    $this->currentUser = $currentUser;
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $response = [];
    $oldAccion = $params['old_accion'];
    $newAccion = $params['new_accion'];

    if ($_SESSION['environment'] == 'movil') {
      $jsonBody = [
        'invoiceDeliveryOption' => $params['invoiceDeliveryOption'],
        'invoiceDetailOption' => $params['invoiceDetailOption'],
      ];

      $jsonBody = json_encode($jsonBody);

      $params_service = [
        'tokens' => [
          'contractId' => $params['contractId'],
        ],
        'body' => $jsonBody,
      ];

      try {
        $response = $this->api->putPaperlessInvoice($params_service);
        if (!empty($response)) {
          drupal_set_message('Hemos recibido tu solicitud exitosamente.');
          // Delete cache.
          $params_cache['tokens'] = [
            'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
          ];
          $this->delete_cache($params_cache, 'getPaperlessInvoiceStatusV2');

          // Send message.
          $this->sendMessage($params);

          // Save audit log.
          $this->saveAuditLog($params);

          // Save segment track change option
          if ($oldAccion !== $newAccion) {
            $this->sendSegmentTrack($params, 'movil');
          }

          // Save segment change type
          if (isset($params['invoiceDetailOptionChange'])) {
            $label = ($params['invoiceDetailOption'] == 'SUMMARY') ? 'Resumida' : 'Detallada';
            $this->sendSegmentTrack($params, 'movil', 0, $label);
          }
        }
        else {
          drupal_set_message('En este momento no podemos procesar tu solicitud, por favor intente más tarde', 'error');
        }
        return new ResourceResponse([]);
      }
      catch (\Exception $e) {
        $mensaje = UtilMessage::getMessage($e);
        // Set message error.
        drupal_set_message($mensaje['message'], 'error');
        // Return message in rest.
        return new ResourceResponse($mensaje['message']);
      }
    }
    elseif ($_SESSION['environment'] == 'fijo') {
      $params['ip'] = \Drupal::request()->getClientIp();
      $params['customerId'] = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

      $jsonBody = [
        "registeringAppName" => "CanalesAlternos",
        "customerIpAddress" => $params['ip'],
        "customerId" => $params['customerId'],
        "billingId" => '1',
        "email" => $params['email'],
        "alternateEmail" => '',
        "phoneNumber" => '',
      ];

      $jsonBody = json_encode($jsonBody);

      $params_service = [
        'tokens' => [
          'contractId' => $params['contractId'],
        ],
        'body' => $jsonBody,
      ];

      if ($params['type'] == 'delete') {
        $jsonBody = [
          'registeringAppName' => 'CanalesAlternos',
          "customerIpAddress" => $params['ip'],
          "customerId" => $params['customerId'],
          'unsubscribeReasonId' => '2',
        ];

        $jsonBody = json_encode($jsonBody);

        $params_service['body'] = $jsonBody;
      }

      switch ($params['type']) {
        case 'register':
          $service_rest = 'createPaperlessInvoice';
          break;

        case 'update':
          $service_rest = 'updatePaperlessInvoice';
          break;

        case 'delete':
          $service_rest = 'deletePaperlessInvoice';
          break;
      }

      try {
        $response = $this->api->$service_rest($params_service);
        if (!empty($response)) {
          // Set message in drupal_set_message().
          $this->setMessage();

          if ($params['type'] == 'delete' || $params['type'] == 'update') {
            // Delete cache service recurringInfoByContractId.
            $params_cache['tokens'] = [
              'contractId' => isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '',
            ];
            $this->delete_cache($params_cache, 'paperlessByContractId');
          }

          // Send message.
          $this->sendMessage($params);

          // Save audit log.
          $this->saveAuditLog($params);

          // Save segment track change option
          if ($oldAccion !== $newAccion) {
            $this->sendSegmentTrack($params, 'fijo');
          }

          // Return for reload.
          return new ResourceResponse([]);
        }
        else {
          drupal_set_message('En este momento no podemos procesar tu solicitud, por favor intente más tarde', 'error');
        }
      }
      catch (\Exception $e) {
        $mensaje = UtilMessage::getMessage($e);
        // Set message error.
        drupal_set_message($mensaje['message'], 'error');
        // Return message in rest.
        return new ResourceResponse($mensaje['message']);
      }
    }

    return new ResourceResponse($response);
  }

  /**
   *
   */
  public function delete_cache($params, $service) {
    // Remove cache.
    $tokens = $query = [];
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['query'])) {
      $query = $params['query'];
    }

    BaseApiCache::delete('service', $service, array_merge($tokens, $query));
  }

  /**
   *
   */
  public function setMessage() {
    drupal_set_message('Hemos recibido tu solicitud exitosamente.');
  }

  /**
   *
   */
  public function saveAuditLog($params) {
    $service = \Drupal::service('tbo_billing.delivery');
    $action = '';
    switch ($params['accion']) {
      case 'desactivación':
        $action = 'desactivo';
        break;

      case 'actualización':
        $action = 'actualizo';
        break;

      case 'activación':
        $action = 'activo';
        break;
    }

    $audit = [
      'event_type' => 'Facturación',
      'description' => 'Usuario ' . $action . ' envió de factura ' . $params['new_accion'],
      'details' => 'Usuario ' . $service->getName() . ' ' . $action . ' él envió de la factura ' . $_SESSION['sendDetail']['paymentReference'] . ' ' . $params['old_accion'] . ' a forma ' . $params['new_accion'],
    ];

    $service->saveAuditLog($audit);
  }

  /**
   * @param $params
   */
  public function sendMessage($params) {
    // Send message.
    $emails = \Drupal::service('tbo_account.repository')->getAllEmailUserCompany($_SESSION['company']['id']);
    $service_message = \Drupal::service('tbo_mail.send');
    $current_uri = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];

    foreach ($emails as $email) {
      $tokens['user'] = $email->full_name;
      $tokens['bill_status'] = $params['accion'];
      $tokens['bill_number'] = $_SESSION['sendDetail']['paymentReference'];
      $tokens['bill_old'] = $params['old_accion'];
      $tokens['bill_new'] = $params['new_accion'];
      $tokens['link'] = $current_uri;
      $tokens['mail_to_send'] = $email->mail;
      $send = $service_message->send_message($tokens, 'config_bill');
    }
  }

  public function sendSegmentTrack($options, $environment, $track = 1, $type = '') {
    if ($track == 1) {
      $event = 'TBO - Modificar Envío - Tx';
      $type = 'Digital';
      if ($options['invoiceDeliveryOption'] == 'PRINTED' || $options['type'] == 'delete') {
        $type = 'Impresa';
      }
      elseif ($options['invoiceDeliveryOption'] == 'BOTH' || $options['type'] == 'register') {
        $type = 'Ambas';
      }
    }
    else {
      $event = 'TBO - Modificar Tipo - Tx';
    }

    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    if (isset($tigoId)) {
      $segment_track = [
        'event' => $event,
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Detalle de Factura',
          'label' => $type . ' - ' . $environment,
          'site' => 'NEW',
        ],
      ];

      $this->segment->track($segment_track);
    }
  }

}
