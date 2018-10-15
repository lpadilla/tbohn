<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;
use Drupal\rest\ResourceResponse;

/**
 * Class AddCardRestService.
 *
 * @package Drupal\tbo_billing\Services
 */
class AddCardRestService {

  protected $api;
  protected $currentUser;
  protected $domiciliationService;
  protected $log;
  protected $segment;
  /**
   * $service_message => Get instance service email.
   */
  protected $service_message;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   * @param \Drupal\tbo_core\Services\AuditLogService $log
   * @param \Drupal\tbo_billing\Services\PaymentDomiciliationService $domiciliationService
   */
  public function __construct(TboApiClientInterface $api, AuditLogService $log, PaymentDomiciliationService $domiciliationService) {
    $this->api = $api;
    $this->log = $log;
    $this->domiciliationService = $domiciliationService;
    $this->service_message = \Drupal::service('tbo_mail.send');
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    $method = 'getCardToken';
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    // Save Audit log.
    $this->log->loadName();
    $name = $this->log->getName();

    $this->saveAuditLog('Usuario solicita agregar nueva tarjeta de crédito', 'Usuario ' . $name . ' solicita agregar nueva tarjeta de crédito');
    $uid = \Drupal::currentUser();
    $mail = $uid->getEmail();

    /*if (isset($_GET['mail'])) {
    $mail = $_GET['mail'];
    }*/

    $phone = str_replace('(', '', $_GET['phone']);
    $phone = str_replace(')', '', $phone);
    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('-', '', $phone);
    $cardNumber = str_replace('-', '', $_GET['card']);
    $month = $_GET['month'];
    $year = $_GET['year'];
    $cardExpirationDate = "$year/$month";

    // Set vars.
    $customer_name = isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '';
    $customer_id = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
    $customer_doc_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    $contract = isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '';

    if ($_SESSION['environment'] == 'fijo') {
      $customerInfo = [
        "customerId" => $customer_id,
        "documentType" => strtoupper($customer_doc_type),
        "name" => $customer_name,
        "lastName" => "",
        "email" => $mail,
        "phoneNumber" => $phone,
      ];

      $cardDetails = [
        "cardNumber" => $cardNumber,
        "cardExpirationDate" => $cardExpirationDate,
        "cardVerificationValue" => $_GET['code'],
        "cardBrand" => strtoupper($_GET['type_card']),
        "cardPrintedName" => $customer_name,
      ];

      $address = [
        "streetAddress" => $_GET['address'],
        "streetAddressLine2" => "",
        "stateId" => "",
        "stateName" => "",
        "districtId" => "",
        "districtName" => "",
        "cityId" => "",
        "cityName" => "",
        "countryId" => "CO",
        "countryName" => "",
        "zoneId" => "050022",
        "stratumId" => "",
      ];

      $params = [
        'customerInfo' => $customerInfo,
        'cardDetails' => $cardDetails,
        'address' => $address,
        'transactionId' => $this->domiciliationService->getTransactionId(),
        'customerIpAddress' => \Drupal::request()->getClientIp(),
      ];

      $jsonBody = json_encode($params);

      $params = [
        'tokens' => [
          'clientId' => $_SESSION['company']['nit'],
          'docType' => $_SESSION['company']['docType'],
        ],
        'body' => $jsonBody,
      ];

      try {
        $response = $this->api->addCreditToken($params);

        // Send segment track.
        $this->sendSegmentTrack('fijo', 'Exitoso');
      }
      catch (\Exception $e) {
        $message = UtilMessage::getMessage($e);
        drupal_set_message($message['message'], "error");

        // Send segment track.
        $this->sendSegmentTrack('fijo', 'Fallido');

        return new ResourceResponse(['message' => 'Error']);
      }
    }
    elseif ($_SESSION['environment'] == 'movil') {
      $payer = [
        'fullName' => $name,
        'email' => $mail,
        'contactPhone' => $phone,
        'dni' => $customer_id,
        'dniType' => strtoupper($customer_doc_type),
        'street1' => $_GET['address'],
        'street2' => '',
        'city' => isset($_SESSION['sendDetail']['city']) ? $_SESSION['sendDetail']['city'] : '',
        'state' => '',
        'country' => 'Colombia',
        'postal' => '',
        'phone' => $phone,
      ];

      $cardNumber = $this->_manage_credit_cards_encrypt($cardNumber);
      $cardExpirationDate = $this->_manage_credit_cards_encrypt($cardExpirationDate);
      $securityCode = $this->_manage_credit_cards_encrypt($_GET['code']);

      if ($cardNumber && $cardExpirationDate && $securityCode) {
        $cc = [
          'name' => $name,
          'number' => $cardNumber,
          'expirationDate' => $cardExpirationDate,
          'securityCode' => $securityCode,
          'paymentMethod' => strtoupper($_GET['type_card']),
          'paymentOnClick' => '0',
        ];

        $params = [
          'payer' => $payer,
          'cc' => $cc,
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

        try {
          $response = $this->api->addCreditCard($params);
          $method = 'getCreditsCardByIdentification';

          // Send segment track.
          $this->sendSegmentTrack('movil', 'Exitoso');
        }
        catch (\Exception $e) {
          $message = UtilMessage::getMessage($e);
          drupal_set_message($message['message'], "error");

          // Send segment track.
          $this->sendSegmentTrack('movil', 'Fallido');

          return new ResourceResponse(['message' => 'Error']);
        }
      }
      else {
        drupal_set_message(t('Ha ocurrido un error. <br /> En este momento no podemos agregar la tarjeta por temas de cifrado, por favor intente de nuevo más tarde'), 'error');
        return new ResourceResponse(['message' => 'Error']);
      }
    }

    if ($response) {
      /**
       * envio de correo de notificacion de desprogramacion de pago programado
       */
      $document_number = $_SESSION['sendDetail']['docNumber'];
      $document_type = $customer_doc_type;
      $enterprise_name = $customer_name;
      // 600006858393.
      $contractId = $_SESSION['sendDetail']['contractId'];
      $cardInfo = substr($_GET['card'], -4);

      \Drupal::service('tbo_billing.payment_domiciliation')->sendEmail($this->service_message, 'add_card_token', $name, $mail, \Drupal::currentUser()->id(), $enterprise_name, $document_number, $document_type, $contractId, $cardInfo, strtoupper($_GET['type_card']));

      $type_response = $_SESSION['recurring_info_payment'];
      $brand = $_GET['brand'];
      // $card_numbers = substr($cardNumber, strlen($cardNumber) - 4, strlen($cardNumber));
      $this->saveAuditLog('Usuario agregó nueva tarjeta de crédito', 'Usuario ' . $name .
        " agregó nueva tarjeta de crédito **** **** **** $cardInfo $brand");

      $this->deleteCache($method);
      if ($type_response) {
        drupal_set_message(t('Proceso exitoso. <br /> Se ha agregado correctamente la tarjeta de crédito @brand  **** **** **** @cardInfo”.', ['@brand' => $brand, '@cardInfo' => $cardInfo]));
        return new ResourceResponse(['message' => 'Sucess', 'url' => isset($_SESSION['detail_invoice_url']) ? $_SESSION['detail_invoice_url'] : '']);
      }
      else {
        $_SESSION['popUp'] = TRUE;
        return new ResourceResponse(['message' => 'Sucess', 'url' => isset($_SESSION['detail_invoice_url']) ? $_SESSION['detail_invoice_url'] : '']);
      }
    }
    else {
      $this->saveAuditLog('Usuario no agregó nueva tarjeta de crédito', 'Usuario ' . $name .
        ' no pudo agregar una nueva tarjeta de crédito. Error retornado por el servicio web a consumir fue 403 y descripción error');
      drupal_set_message(t('Ha ocurrido un error. <br /> En este momento no podemos agregar la tarjeta, por favor intente de nuevo más tarde'), 'error');
      return new ResourceResponse(['message' => 'Error']);
    }
  }

  /**
   *
   */
  public function deleteCache($method) {
    // Delete All cache domiciliation
    // Parameters for service getCardToken.
    $client_id = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
    $params = [
      'tokens' => [
        'docType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
        'clientId' => $client_id,
      ],
      'query' => [],
    ];

    if ($method == 'getCreditsCardByIdentification') {
      $params['tokens'] = [
        'clientId' => $client_id,
      ];
    }

    BaseApiCache::delete('service', $method, array_merge($params['tokens'], $params['query']));
  }

  /**
   * Implements saveAuditLog().
   *
   * @param string $description
   *   Description to save.
   * @param string $details
   *   Details to save.
   */
  public function saveAuditLog($description = '', $details = '') {
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
   * Implements _manage_credit_cards_encrypt().
   *
   * @param string $text
   *   The text to encrypt.
   *
   * @return bool|string
   *   The encrypt value.
   */
  public function _manage_credit_cards_encrypt($text = '') {
    $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
    $key128 = '01235467890ab1de';

    // Here's our 128-bit IV which is used for both 256-bit and 128-bit keys.
    $iv = '1234567890123456';
    $cleartext = $text;
    if (mcrypt_generic_init($cipher, $key128, $iv) != -1) {
      // PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
      $cipherText = mcrypt_generic($cipher, $cleartext);
      mcrypt_generic_deinit($cipher);

      // Display the result in hex.
      return bin2hex($cipherText);
    }
    return FALSE;
  }

  /**
   * Send segment track.
   *
   * @param string $environment
   *   The environment.
   * @param string $status
   *   The status add card.
   */
  public function sendSegmentTrack($environment = '', $status = '') {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    if (isset($tigoId)) {
      $this->segment->track([
        'event' => 'TBO - Agregar Tarjeta de Crédito - Tx',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Pago automático',
          'label' => 'Continuar - ' . $environment . ' - ' . $status,
          'site' => 'NEW',
        ],
      ]);
    }
  }

}
