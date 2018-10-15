<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Util\UtilArray;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class InvoiceHistoryRestService.
 *
 * @package Drupal\tbo_billing\Services
 */
class InvoiceHistoryRestService {

  private $api;
  protected $currentUser;
  private $tbo_config;

  /**
   * CurrentInvoiceService constructor.
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
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $response = [];

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    if ($_SESSION['environment'] == 'fijo') {
      $response = $this->getfixedInvoicesByContractId();
    }
    elseif ($_SESSION['environment'] == 'movil') {
      $response = $this->getMobileInvoicesByContractId();
    }

    if ($response) {
      return new ResourceResponse($response);
    }

    return new ResourceResponse("La consulta no se puede realizar.");
  }

  /**
   * Get mobile invoices history by contractId.
   *
   * @return array|bool|ResourceResponse
   */
  public function getMobileInvoicesByContractId() {
    try {
      // Set params for service.
      $params['query'] = [
        'clientType' => strtoupper($_SESSION['company']['docType']),
        'countInvoiceToReturn' => 6,
        'endDate' => date('d/m/Y', time()),
        'type' => 'mobile',
        'contractNumber' => $_SESSION['sendDetail']['contractId'],
      ];

      $params['tokens'] = [
        'clientId' => $_SESSION['company']['nit'],
      ];

      try {
        $response = $this->api->getBillingInformation($params);
      }
      catch (\Exception $e) {
        // Return message in rest.
        return UtilMessage::getMessage($e);
      }

      // Save Audit log.
      $this->saveAuditLog();

      if (is_object($response) && isset($response->billingAccountCollection->billingAccount)) {
        $billing_accounts = UtilArray::tbo_util_unique_object_to_array($response->billingAccountCollection->billingAccount);

        $data = ['billing_accounts' => $billing_accounts];
        $dataresponse = [];
        if ($billing_accounts) {
          foreach ($billing_accounts as $value) {
            foreach ($value->invoiceCollection->invoice as $generate) {
              $invoice_history = [];
              $invoice_history['contractId'] = $generate->invoiceNumber;
              $invoice_history['contractOfSendDetails'] = $_SESSION['sendDetail']['contractId'];
              $invoice_history['invoiceId'] = $generate->invoiceSerial;
              $invoice_history['invoiceNumber'] = $generate->invoiceNumber;
              $invoice_history['dueDate'] = $this->tbo_config->formatDate(strtotime($generate->expirationDate));
              $status = 'Pagada';
              if (strtoupper($generate->invoiceStatus) != 'PAID' && date("Y-m-d-H:m") > $generate->expirationDate) {
                $status = 'Vencida';
              }
              if (strtoupper($generate->invoiceStatus) != 'PAID' && date("Y-m-d-H:m") < $generate->expirationDate) {
                $status = '';
              }
              $invoice_history['status'] = $status;
              $invoice_history['invoiceAmount'] = $this->tbo_config->formatCurrency($generate->invoiceAmount);
              array_push($dataresponse, $invoice_history);
            }
          }

          return $dataresponse;
        }
      }
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    return FALSE;

  }

  /**
   * Get fixed invoices history by contractId.
   *
   * @return array|bool|ResourceResponse
   */
  public function getfixedInvoicesByContractId() {
    $service = __FUNCTION__;
    $contractId = $_SESSION['sendDetail']['contractId'];
    try {
      $params = [
        'query' => [
      // Todo. El valor maximo que acepta el servicio para el limite es 12.
          'quantity' => 12,
        ],
        'tokens' => [
          'contractId' => $contractId,
        ],
      ];

      // Get data.
      try {
        $response = $this->api->findCustomerBillsHistoryByAccountId($params);
      }
      catch (\Exception $e) {
        // Return message in rest.
        return UtilMessage::getMessage($e);
      }

      // Save Audit log.
      $this->saveAuditLog();

      $dataresponse = [];
      if ($response) {
        $counter = 1;

        foreach ($response as $value) {
          $invoice_history = [];
          $invoice_history['contractId'] = $contractId;
          $invoice_history['invoiceId'] = $value->billNo;
          $paymentDueDate = $value->paymentDueDate;
          $paymentDueDate_exp = str_replace('/', '-', $paymentDueDate);
          $invoice_history['dueDate'] = $this->tbo_config->formatDate(strtotime($paymentDueDate_exp));

          $date = str_replace('/', '-', $value->billDate);
          $date_bill = date('Y-m-d', strtotime($date));
          $invoice_history['billDate'] = $date_bill;

          if ($value->billStatus == TRUE) {
            $status = t('Pagada');
          }
          else {
            $status = t('Sin pago');
          }
          if (!$value->billStatus && strtotime(date("d-m-Y")) > strtotime($paymentDueDate_exp)) {
            $status = 'Vencida';
          }
          if (!$value->billStatus && strtotime(date("d-m-Y")) < strtotime($paymentDueDate_exp)) {
            $status = '';
          }
          $invoice_history['status'] = $status;
          $invoiceAmount = $value->billAmount;
          $invoice_history['invoiceAmount'] = $this->tbo_config->formatCurrency($invoiceAmount);
          $invoice_history['counter'] = $counter;
          $counter = $counter + 1;
          array_push($dataresponse, $invoice_history);
        }

        return $dataresponse;
      }
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    return FALSE;

  }

  /**
   * Guardado log auditoria.
   */
  public function saveAuditLog() {
    // Save Audit log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    $factura = isset($_SESSION['sendDetail']['paymentReference']) ? $_SESSION['sendDetail']['paymentReference'] : '';
    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturación',
      'description' => 'Usuario accede al histórico de factura',
      'details' => 'Usuario ' . $name . ' accedió  al  histórico  de  la  factura ' . $factura,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $service_log->insertGenericLog($data);
  }

}
