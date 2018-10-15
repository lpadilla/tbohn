<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\rest\ResourceResponse;

/**
 * Class MultipleInvoiceRestService.
 *
 * @package Drupal\tbo_billing\Services
 */
class MultipleInvoiceRestService {

  private $api;
  protected $currentUser;
  private $tbo_config;

  /**
   * MultipleInvoiceRestService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $data) {
    $_SESSION['multiple_payment']['data'] = $data;
    $this->currentUser = $currentUser;
    $account_fields = $currentUser->getAccount();
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $params = [];
    $body = [];
    $tokens = [
      'docType' => $account_fields->document_type,
      'clientId' => $account_fields->document_number,
    ];

    if (count($data) == 1) {
      $invoiceRef = $data[0]['payment_reference'];
      $contractId = $data[0]['contract'];
      $monto = $data[0]['invoice_value'];
      $description = "Referencia:$invoiceRef - Contrato:$contractId - Valor:$monto";
    }
    // Body para servicio post checkoutPayments.
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = $currentUser->getAccountName();
    }

    if (isset($account_fields->phone_number) && !empty($account_fields->phone_number)) {
      $phone = $account_fields->phone_number;
    }
    else {
      $phone = t('00000000');
    }

    $clientInformation = [
      'firstName' => $name,
      'lastName' => $name,
      'email' => $currentUser->getEmail(),
      'phone' => $phone,
      'userIp' => NULL,
    ];

    $billsCollection = [];
    $total_amount = 0;

    foreach ($data as $invoice) {
      $aux_invoice = [
        "billNo" => $invoice['invoiceId'],
        "amount" => NULL,
        "startingDate" => NULL,
        "endDate" => NULL,
        "description" => isset($description) ? $description : NULL,
      ];
      $total_amount = $total_amount + intval($invoice['invoice_value']);
      array_push($billsCollection, $aux_invoice);
    };

    $paymentData = [
      'method' => ['PSE', 'POL'],
      'description' => t('Pago Múltiple de factura - Portal AutoGestion B2B'),
      'totalAmount' => "$total_amount",
      'taxValue' => NULL,
      'totalValueWithTax' => isset($monto) ? "$monto" : NULL,
      'baseValue' => NULL,
    ];

    $body = [
      'intent' => 'create',
      'clientInformation' => $clientInformation,
      'billsCollection' => $billsCollection,
      'paymentData' => $paymentData,
      'confirmationUrl' => $GLOBALS['base_url'] . '/pagos/fijo/co/respuesta/?',
    ];

    $params['tokens'] = $tokens;
    $params['body'] = json_encode($body);

    try {
      $response = $this->api->checkoutPayments($params);
      if (!isset($response->signature) && !isset($response->url)) {
        $response = ['error' => 'Error en el servicio'];
      }
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }
    $_SESSION['multiple_payment']['response'] = $response;
    return (json_encode($response));
  }

}
