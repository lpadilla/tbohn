<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class BillingPaymentService.
 *
 * @package Drupal\tbo_billing
 */
class BillingPaymentService implements BillingPaymentInterface {

  private $api;
  private $tbo_config;

  /**
   * Constructor.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /**
   *
   */
  public function payBilling($contractId, $invoiceRef, $type, $monto, $fecha, $msisdn) {
    $urlResponse = $_SERVER['HTTP_REFERER'];
    unset($_SESSION['multiple_payment']);
    $config = \Drupal::config('tbo_billing.bill_payment_settings');
    if ($type == 'movil') {
      $uid = \Drupal::currentUser();
      $mail = $uid->getEmail();
      $config = \Drupal::config("tbo_general_co.mobilepaymenturl");
      $url = $config->get('payment_url');
      $clientId = $config->get('client_id');
      $urlResponse = "$url?client_id=$clientId&msisdn=$msisdn&reference_code=$invoiceRef&email=$mail&value=$monto&due_date=$fecha";
      BaseApiCache::set('payBilling', $invoiceRef, '', $urlResponse, $expire = 900);

    }
    elseif ($type == 'fijo') {
      $currentUser = \Drupal::currentUser();
      $account_fields = $currentUser->getAccount();
      // Remove cache.
      \Drupal::service('page_cache_kill_switch')->trigger();
      $params = [];
      $body = [];
      $tokens = [
        'docType' => $account_fields->document_type,
        'clientId' => $account_fields->document_number,
      ];

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

      $aux_invoice = [
        "billNo" => $invoiceRef,
        "amount" => NULL,
        "startingDate" => NULL,
        "endDate" => NULL,
        "description" => "Referencia:$invoiceRef - Contrato:$contractId - Valor:$monto",
      ];
      $total_amount = $monto;
      array_push($billsCollection, $aux_invoice);

      $paymentData = [
        'method' => ['PSE', 'POL'],
        'description' => t('Pago Sencillo de factura - Portal AutoGestion B2B'),
        'totalAmount' => $total_amount,
        'taxValue' => 0,
        'totalValueWithTax' => $monto,
        'baseValue' => 0,
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
        $response = $this->api->checkoutPaymentsSimple($params);
        $_SESSION['simple_payment']['response'] = $response;
        $_SESSION['simple_payment']['data'] = $params;
        $_SESSION['simple_payment']['data']['date'] = $fecha;
        $_SESSION['simple_payment']['data']['contract'] = $contractId;
        $_SESSION['simple_payment']['data']['invoice'] = $invoiceRef;
        if (isset($response->signature) && isset($response->url)) {
          $urlResponse = $response->url;
        }
      }
      catch (\Exception $e) {
        // Return message in rest
        // return new ResourceResponse(UtilMessage::getMessage($e));
      }
    }
    return $urlResponse;
  }

  /**
   *
   */
  public function saveCheckoutAuditLog($type, $invoiceId, $paymentStatus) {
    /**
     * get user info log activity
     */
    $current_user = \Drupal::currentUser();
    $user_names = $current_user->getAccountName();

    if ($paymentStatus == 'APPROVED') {
      $detalles = t('El usuario ' . $user_names . ' pagó la factura ' . $type . ' ' . $invoiceId);
    }
    if ($paymentStatus == 'PENDING') {
      $detalles = t('La factura ' . $type . ' ' . $invoiceId . ' quedó en estado pendiente');
    }
    if ($paymentStatus == 'DECLINED') {
      $detalles = t('La factura ' . $type . ' ' . $invoiceId . ' no pudo pagarse exitosamente');
    }

    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => t('Facturación'),
      'description' => ($type == 'fijo') ? t('Pago de factura Fija') : t('Pago de factura Móvil'),
      'details' => $detalles,
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];

    $service->insertGenericLog($data);

  }

}
