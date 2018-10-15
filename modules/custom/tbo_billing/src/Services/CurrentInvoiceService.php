<?php

namespace Drupal\tbo_billing\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class CurrentInvoiceService.
 *
 * @package Drupal\tbo_billing\Services
 */
class CurrentInvoiceService implements CurrentInvoiceServiceInterface {
  protected $api;
  protected $currentUser;
  protected $tbo_config;

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
    $uri_billing = \Drupal::request()->getRequestUri();
    $billing_type = $_GET['billing_type'];
    $billing_contract = NULL;
    if (isset($_GET['billing_contract'])) {
      $billing_contract = $_GET['billing_contract'];
    }
    $data = $this->getAllInvoices($billing_type, $billing_contract);
    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data_log[].
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturación',
      'description' => t('Usuario consulta listado de factutas vigentes'),
      'details' => 'Usuario ' . $service->getName() . ' consultó listado de facturas vigentes',
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);
    return $data;
  }

  /**
   * @param $billing_type
   * @param $billing_contract
   * @return \Drupal\rest\ResourceResponse|string
   */
  public function getAllInvoices($billing_type, $billing_contract = NULL) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    // Missed current language, set default as spanish.
    setlocale(LC_ALL, 'es_ES');
    $date = date('Y-m');
    $current_date = date('Y-m-d');
    $max_date_page = date('Y-m-d', strtotime($current_date . ' - 10 days'));
    $alert = TRUE;
    $result = '';
    $status_invoice = TRUE;
    if (isset($_SESSION['company'])) {
      $clientId = $_SESSION['company']['nit'];
      $company_document = $_SESSION['company']['docType'];
    }
    else {
      $result = "no se ha seleccionado ninguna empresa";
    }
    if ($billing_type == 'fijo') {
      $data = [];
      $data_filters_contracts = [];
      $data_filters_address = [];
      $params['query'] = [
        'reg_ini' => 1,
        'num_reg' => 100,
      ];
      $params['tokens'] = [
        'docType' => $company_document,
        'clientId' => $clientId,
      ];
      try {
        $response = $this->api->findCustomerAccountsByIdentification($params);
      }
      catch (\Exception $e) {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
      $invoices = [];
      foreach ($response->contractsCollection as $customerAccount) {
        foreach ($customerAccount->billsCollection as $invoice) {
          $status_invoice = TRUE;
          $alert = TRUE;
          if ($invoice->billStatus == TRUE) {
            $status = t('PAGADA');
            $status_invoice = TRUE;
          }
          else {
            $status = t('SIN PAGO');
            $status_invoice = FALSE;
          }
          $paymentDueDate = $invoice->paymentDueDate;
          $paymentDueDate_exp = str_replace('/', '-', $paymentDueDate);
          // If (date('Y-m', strtotime($paymentDueDate_exp)) == $date) { // TODO eliminar al aprobar jira 192.
          $contractId = $customerAccount->contractId;
          array_push($data_filters_contracts, ['name' => $contractId]);
          $date_invoice = $this->tbo_config->formatDate(strtotime($paymentDueDate_exp));
          $date_status = '';
          // Generate status.
          if (date('Y-m-d', strtotime($date_invoice)) >= $current_date && $status == 'SIN PAGO') {
            $date_status = 'slopes';
          }
          elseif ($current_date > date('Y-m-d', strtotime($date_invoice)) && $status == 'SIN PAGO') {
            $date_status = 'overdue';
          }
          elseif ($status == 'PAGADA') {
            $date_status = 'paid';
          }
          elseif (strtoupper($invoice->billClaimStatus) != 'NORMAL') {
            $date_status = 'adjusted';
          }
          if (date('Y-m-d', strtotime($date_invoice)) < $current_date) {
            $alert = FALSE;
            if (!$invoice->billStatus) {
              $status_invoice = FALSE;
            }
          }
          else {
            $status_invoice = TRUE;
          }
          if (strtoupper($invoice->billClaimStatus) != 'NORMAL') {
            if ($status_invoice) {
              $alert = TRUE;
            }
          }
          if ($invoice->billStatus == TRUE) {
            $alert = FALSE;
          }
          $period_format = strftime('%b %G', strtotime(str_replace('/', '-', $invoice->billDate)));
          $date_stime = str_replace('/', '-', $invoice->billDate);
          $date_bill = date('Y-m-d', strtotime($date_stime));
          $invoice = [
            'address' => $customerAccount->localAddress->address,
            'invoice_value' => $invoice->billAmount,
            'invoice_value2' => $this->tbo_config->formatCurrency($invoice->billAmount),
            'date_payment' => $invoice->paymentDueDate,
            'date_payment2' => $this->tbo_config->formatDate(strtotime($paymentDueDate_exp)),
            'date_payment3' => $date_bill,
            'contract' => $contractId,
            'payment_reference' => $invoice->referencePayment,
            'period' => $period_format,
            'status' => $status,
            'status_invoice' => $status_invoice,
            'adjustment' => $invoice->billClaimStatus,
            'company_document' => $clientId,
            'invoiceId' => $invoice->billNo,
            'addressActual' => $invoice->localAddress->address,
            'city' => $customerAccount->localAddress->geographicPlace->city,
            'alert' => $alert,
            'date_status' => $date_status,
            'state' => 'null',
            'country' => 'null',
            'zipcode' => 'null',
            'address_show' => 1,
            'add_multiple' => 1,
          ];

          if ($status != 'PAGADA') {
            if (substr($paymentDueDate, 3, 2) . '-' . substr($paymentDueDate, 0, 2) == date('m-d')) {
              $invoice['day_payment'] = 0;
            }
            else {
              $current_date_tp = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d');
              $current_date_tp = strtotime($current_date_tp);
              $invoice['day_payment'] = ceil(($current_date_tp - strtotime($paymentDueDate_exp)) / 86400);
            }
          }
          array_push($invoices, $invoice);
          array_push($data_filters_address, ['name' => $customerAccount->localAddress->address]);
          unset($bills);
          // }.
        }
      }
      if (!empty($invoices)) {
        usort($invoices, function ($a1, $a2) {
          $v1 = strtotime(str_replace('/', '-', $a1['date_payment']));
          $v2 = strtotime(str_replace('/', '-', $a2['date_payment']));
          // $v2 - $v1 to reverse direction.
          return $v1 - $v2;
        });
      }

      unset($customerAccount);
      $invoices = $this->groupInvoices($invoices, 'address');
      array_push($data, $this->deleteDuplicates($data_filters_contracts, 'name'));
      array_push($data, $invoices);
      array_push($data, $this->deleteDuplicates($data_filters_address, 'name'));
      $result = new ResourceResponse($data);
    }
    elseif ($billing_type == 'movil') {
      $current_date = date('Y-m-d');
      $day = date('d');
      $month = date('m');
      $year = date('Y');
      $max_date_page = date('Y-m-d', strtotime(date('Y-m-d') . ' - 10 days'));
      $pastYear = $year - 1;
      $nextYear = $year + 1;
      $params['query'] = [
        'clientType' => strtoupper($company_document),
        'countInvoiceToReturn' => 1,
        'startDate' => date("d/m/Y", strtotime("first day of today -6 months")),
        'endDate' => "$day%2F$month%2F$nextYear",
        'type' => 'mobile',
      ];
      $params['tokens'] = [
        'clientId' => $clientId,
      ];
      $account_contract = [];
      $data_filters_address = [];
      $account_data = [];
      $data = [];
      try {
        $response = $this->api->getBillingInformation($params);
      }
      catch (\Exception $e) {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
      $company_document = $response->clientDocument->id;
      // Validate quantity billingAccount to foreach.
      if ($response) {
        $for_data = $response->billingAccountCollection->billingAccount;
        if (count($response->billingAccountCollection->billingAccount) <= 1) {
          $for_data = $response->billingAccountCollection;
        }
        foreach ($for_data as $value) {
          array_push($account_contract, ['name' => $value->billingAccountId]);
          // Data for service getContractMSISDN.
          $params['query'] = [
            'operation' => 'getContractMSISDN',
            'intialResultNumber' => 1,
            'resultsPerPage' => 1,
          ];
          $params['tokens'] = [
            'contractId' => $value->billingAccountId,
          ];
          try {
            $msisdn = $this->api->getContractMSISDN($params);
            if (!$msisdn) {
              $msisdn = 'No Disponible';
            }
          }
          catch (\Exception $e) {
            $msisdn = 'No Disponible';
          }
          $for_invoice = $value->invoiceCollection->invoice;
          if (count($value->invoiceCollection) <= 1) {
            $for_invoice = $value->invoiceCollection;
          }
          foreach ($for_invoice as $invoice) {
            if ($invoice->invoiceStatus == 'PAID') {
              $status = t('PAGADA');
              $status_invoice = TRUE;
            }
            else {
              $status = t('SIN PAGO');
              $status_invoice = FALSE;
            }
            $date_invoice = date('Y-m', strtotime($invoice->expirationDate));
            $date_payment_format = strftime('%e %b %G', strtotime($invoice->expirationDate));
            $period_format = strftime('%b %G', strtotime(substr($invoice->billPeriod, 0, 9)));
            // If ($date_invoice == $date) { // TODO eliminar al aprobar jira 192.
            $paymentDueDate = $invoice->expirationDate;
            $paymentDueDate_exp = str_replace('/', '-', $paymentDueDate);
            $date_invoice = $this->tbo_config->formatDate(strtotime($paymentDueDate_exp));
            if (date('Y-m-d', strtotime($invoice->expirationDate)) < $current_date) {
              $alert = FALSE;
              if ($invoice->invoiceStatus != 'PAID') {
                $status_invoice = FALSE;
              }
            }
            else {
              $status_invoice = TRUE;
            }
            $date_status = '';
            // Generate status.
            if (date('Y-m-d', strtotime($invoice->expirationDate)) >= $current_date && $status == 'SIN PAGO') {
              $date_status = 'slopes';
            }
            elseif ($current_date > date('Y-m-d', strtotime($invoice->expirationDate)) && $status == 'SIN PAGO') {
              $date_status = 'overdue';
            }
            elseif ($status == 'PAGADA') {
              $date_status = 'paid';
            }
            $number_formated = $this->tbo_config->formatCurrency($value->debtAmount);
            $the_invoice = [
              'address' => $msisdn,
              'invoice_value' => isset($value->debtAmount) ? $value->debtAmount : t('No disponible'),
              'invoice_value2' => isset($number_formated) ? $number_formated : t('No disponible'),
              'date_payment' => $invoice->expirationDate,
              'date_payment2' => $date_payment_format,
              'contract' => $invoice->contract,
              'payment_reference' => $invoice->contract,
              'period' => ucfirst($period_format),
              'status' => $status,
              'status_invoice' => $status_invoice,
              'adjustment' => FALSE,
              'company_document' => $company_document,
              'invoiceId' => $invoice->invoiceNumber,
              'addressActual' => $invoice->billingAddress->Street,
              'city' => $invoice->billingAddress->City,
              'alert' => $alert,
              'date_status' => $date_status,
              'state' => $invoice->billingAddress->State,
              'country' => $invoice->billingAddress->Country,
              'zipcode' => $invoice->billingAddress->ZIPCode,
              'address_show' => 1,
            ];
            if ($status != 'PAGADA') {
              if (date('m-d', strtotime($the_invoice['date_payment2'])) == date('m-d')) {
                $the_invoice['day_payment'] = 0;
              }
              else {
                $current_date_tp = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d');
                $current_date_tp = strtotime($current_date_tp);
                $the_invoice['day_payment'] = ceil(($current_date_tp - strtotime($the_invoice['date_payment2'])) / 86400);
              }
            }

            array_push($account_data, $the_invoice);
            // }.
            array_push($data_filters_address, ['name' => $msisdn]);
          };
        }
        usort($account_data, function ($a1, $a2) {
          $v1 = strtotime($a1['date_payment']);
          $v2 = strtotime($a2['date_payment']);
          // $v2 - $v1 to reverse direction.
          return $v1 - $v2;
        });
      }
      $account_data = $this->groupInvoices($account_data, 'address');
      array_push($data, $this->deleteDuplicates($account_contract, 'name'));
      array_push($data, $account_data);
      array_push($data, $this->deleteDuplicates($data_filters_address, 'name'));
      $result = new ResourceResponse($data);
    }
    return $result;
  }

  /**
   *
   */
  public function deleteDuplicates($array, $campo) {
    $new = [];
    $exclude = [""];
    for ($i = 0; $i <= count($array) - 1; $i++) {
      if (!in_array(trim($array[$i][$campo]), $exclude)) {
        $new[] = $array[$i];
        $exclude[] = trim($array[$i][$campo]);
      }
    }
    return $new;
  }

  /**
   *
   */
  public function groupInvoices($input, $sortkey) {
    $aux = [];
    $output = [];

    foreach ($input as $key => $val) {
      $aux[$val[$sortkey]][] = $val;
    }

    foreach ($aux as $value) {
      if (count($value) == 1) {
        array_push($output, $value[0]);
      }
      else {
        for ($i = 0; $i < count($value); $i++) {
          if ($i >= 1) {
            $value[$i]['address_show'] = 0;
          }
          array_push($output, $value[$i]);
        }
      }
    }
    return $output;
  }

}
