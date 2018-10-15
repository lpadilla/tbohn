<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_core\Base\BaseApiCache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BillingSummaryService.
 *
 * @package Drupal\tbo_billing
 */
class BillingSummaryService {

  private $tbo_config;
  protected $api;
  protected $clientId;
  protected $company_document;
  protected $segment;
  protected $cache;

  /**
   * Constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    \Drupal::service('adf_segment')->segmentPhpInit();
    $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
    $this->cache = new BaseApiCache();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = $this->getSummary($_GET['type']);

    return new ResourceResponse($response);
  }

  /**
   * Obtener el sumario de facturas dado un tipo.
   * @param $type
   * @return array|string
   */
  public function getSummary($type) {
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::service('current_user')->id());
    $response = [];

    if (isset($_SESSION['company'])) {
      $this->clientId = $_SESSION['company']['nit'];
      $this->company_document = $_SESSION['company']['docType'];
      if (($type == 'movil') && ($_SESSION['company']['environment'] == "movil" || $_SESSION['company']['environment'] == "both")) {
        $response = $this->getBillingSummaryMobile();
      }
      elseif (($type == 'fijo') && ($_SESSION['company']['environment'] == "fijo" || $_SESSION['company']['environment'] == "both")) {
        $response = $this->getBillingSummaryFixed();
      }

      if ($_SESSION['company']['environment'] == 'both') {
        $this->cache->setGlobal('dashboard_' . $type, ['load' => TRUE, 'value' => $response['segment_amount']]);
        $movil_segment = $this->cache->getGlobal('dashboard_movil');
        $fijo_segment = $this->cache->getGlobal('dashboard_fijo');

        if ($movil_segment['load'] == TRUE && $fijo_segment['load'] == TRUE) {
          try {
            if (isset($tigoId)) {
              $value = $movil_segment['value'] + $fijo_segment['value'];
              $this->segment->track([
                'event' => 'TBO - Visualizar Dashboard - Consulta',
                'userId' => $tigoId,
                'properties' => [
                  'category' => 'Dashboard',
                  'label' => 'movil - fijo',
                  'value' => $value,
                  'site' => 'NEW',
                ],
              ]);
            }
          }
          catch (\Exception $e) {
            // It is pending to define the process to be carried out in the exception.
          }

          $this->cache->deleteGlobal('dashboard_fijo');
          $this->cache->deleteGlobal('dashboard_movil');
        }
      }
      elseif ($_SESSION['company']['environment'] == $type) {
        try {
          if (isset($tigoId)) {
            $this->segment->track([
              'event' => 'TBO - Visualizar Dashboard - Consulta',
              'userId' => $tigoId,
              'properties' => [
                'category' => 'Dashboard',
                'label' => $_SESSION['company']['environment'],
                'value' => $response['segment_amount'],
                'site' => 'NEW',
              ],
            ]);
          }
        }
        catch (\Exception $e) {
          // It is pending to define the process
          // to be carried out in the exception.
        }
      }
    }
    else {
      return "no se ha seleccionado ninguna empresa";
    }

    return $response;
  }

  /**
   * Obtener el sumario de facturas del servicio móvil para el usuario.
   * @return array
   */
  public function getBillingSummaryMobile() {
    $response = [];
    $date = date('Y-m');
    $dateExact = date('Y-m-d');
    $max_date_page = date('Y-m-d', strtotime($dateExact . ' - 10 days'));
    $totalInvoices = $totalAmount = 0;
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $nextYear = $year + 1;
    $params['query'] = [
      'clientType' => strtoupper($this->company_document),
      'countInvoiceToReturn' => 1,
      'startDate' => date("d/m/Y", strtotime("first day of today -6 months")),
      'endDate' => "$day%2F$month%2F$nextYear",
      'type' => 'mobile',
    ];
    $params['tokens'] = [
      'clientId' => $this->clientId,
    ];
    try {
      $response = $this->api->getBillingInformation($params);
    }
    catch (\Exception $e) {
      // Return message in rest.
      return UtilMessage::getMessage($e);
    }
    // Validate quantity billingAccount to foreach.
    $for_data = $response->billingAccountCollection->billingAccount;
    if (count($response->billingAccountCollection->billingAccount) <= 1) {
      $for_data = $response->billingAccountCollection;
    }
    if ($response) {
      foreach ($for_data as $value) {
        $for_invoice = $value->invoiceCollection->invoice;
        if (count($value->invoiceCollection) <= 1) {
          $for_invoice = $value->invoiceCollection;
        }
        foreach ($for_invoice as $invoice) {
          if ($invoice->invoiceStatus != 'PAID') {
            if (intval($value->debtAmount) > 0) {
              $totalAmount = $totalAmount + intval($value->debtAmount);
              $totalInvoices = $totalInvoices + 1;
            }
          }
        };
      }
    }
    $response = [
      'service' => $this->tbo_config->formatCurrency($totalAmount),
      'body' => $totalInvoices,
      'segment_amount' => $totalAmount,
    ];
    return $response;
  }


  /**
   * Obtiene el resumen de facturas pendientes para el servicio Fijo.
   * @return array
   */
  public function getBillingSummaryFixed() {
    $response = [];
    $date = date('Y-m');
    $dateExact = date('Y-m-d');
    $max_date_page = date('Y-m-d', strtotime($dateExact . ' - 10 days'));
    $totalAmount = 0;
    $totalInvoices = 0;

    $params['query'] = [
      'reg_ini' => 1,
      'num_reg' => 100,
    ];

    $params['tokens'] = [
      'docType' => $this->company_document,
      'clientId' => $this->clientId,
    ];

    try {
      $response = $this->api->findCustomerAccountsByIdentification($params);
    }
    catch (\Exception $e) {
      // Verify if  404 code.
      if ($e->getCode() == Response::HTTP_NOT_FOUND) {
        // Todo ver con el personal de análisis que debería
        // Todo retornar en caso de que no encuentre los datos.
        return array('segment_amount' => 0, 'no-service' => true, 'body'=> 0);
      }
      // Return message in rest.
      return UtilMessage::getMessage($e);
    }

    if ($response) {
      foreach ($response->contractsCollection as $customerAccount) {
        foreach ($customerAccount->billsCollection as $invoice) {
          $status = '';

          if ($invoice->billStatus == TRUE) {
            $status = t('PAGADA');
          }
          else {
            $status = t('SIN PAGO');
          }

          if ($status == 'SIN PAGO') {
            if (intval($invoice->billAmount) > 0) {
              $totalAmount = $totalAmount + intval($invoice->billAmount);
              $totalInvoices = $totalInvoices + 1;
            }
          }
        }
      }
      $response = [
        'service' => $this->tbo_config->formatCurrency($totalAmount),
        'body' => $totalInvoices,
        'segment_amount' => $totalAmount,
      ];
    }
    return $response;
  }

  public function serviceResponseCode($code){
    $response = FALSE;
    $params['query'] = [
      'reg_ini' => 1,
      'num_reg' => 100,
    ];
    $params['tokens'] = [
      'docType' => $_SESSION['company']['docType'],
      'clientId' => $_SESSION['company']['nit'],
    ];

    try {
      $response = $this->api->findCustomerAccountsByIdentification($params);
    }catch (\Exception $e){
      if (!in_array($e->getCode(), $code)){
        $response = TRUE;
      }
    }
    return $response;
  }

}
