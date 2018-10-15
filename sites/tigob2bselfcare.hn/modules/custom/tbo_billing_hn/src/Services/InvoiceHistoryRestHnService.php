<?php

namespace Drupal\tbo_billing_hn\Services;

use Drupal\tbo_billing\Services\InvoiceHistoryRestService;
use Drupal\adf_core\Util\UtilArray;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class InvoiceHistoryRestHnService.
 *
 * @package Drupal\tbo_billing_hn\Services
 */
class InvoiceHistoryRestHnService extends InvoiceHistoryRestService {

  private $api;
  protected $currentUser;
  private $tbo_config;

  /**
   * CurrentInvoiceService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /**
   * @param AccountProxyInterface $currentUser
   * @return ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();
    $response = [];

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $response = $this->getInvoicesByContractId();

    if ($response) {
      return new ResourceResponse($response);
    }

    return new ResourceResponse("La consulta no se puede realizar.");
  }

  /**
   * get invoices history by contractId
   *
   * @return array|bool|ResourceResponse
   */
  public function getInvoicesByContractId() {
    
    try {
      $clientId = $_SESSION['sendDetail']['contractId'];
      
      $msisdnSesion = $_SESSION['sendDetail']['invoice']['msisdn'];
      
      $company_code = false;
      if (isset($_SESSION['company'])) {
	      $company_code = $_SESSION['company']['company_code'];  
	    }
	    
      $config = \Drupal::config("tbo_billing_hn.bill_payment_settings");
	    $group = "visualizacion";
	    
	    $params['query'] = [
	      'clientId' => $clientId,
	      'companyCode' => $company_code,
	      'quantity' => $config->get($group)['servicehistoryparam']['billingquantityhistory'],
	      'end' => date('Y-m-d'),
	      'offset' => 1,
	      'limit' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
	      
	    ];
	    
	    $params['tokens'] = [
	      'clientId' => $clientId,
	      'companyCode' => $company_code,
	      'quantity' => $config->get($group)['servicehistoryparam']['billingquantityhistory'],
	      'end' => date('Y-m-d'),
	      'offset' => 1,
	      'limit' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
	      
	     ];
       
        $params['headers'] = [ 
          'cache-control' => 'no-cache',  
          'x-debug' => 'true',       
        ];
  
      try {
        $response = $this->api->getBillingInfoPropossal($params);
        
      }
      catch (\Exception $e) {
        //return message in rest
        return UtilMessage::getMessage($e);
      }
      
      //Save Audit log
      $this->saveAuditLog();
       
      if (isset($response->billsDetails->billingGroupCollection)) { //cambios en el servicio!!!! RVS 20171213
        $billing_accounts = UtilArray::tbo_util_unique_object_to_array($response->billsDetails->billingGroupCollection); 
        
        $data = array('billing_accounts' => $billing_accounts); 
        $dataresponse = [];
        if ($billing_accounts) {
          foreach ($billing_accounts as $value) {         
            foreach ($value->billsCollection as $generate) {

              $invoice_history = [];              
              if (trim($generate->msisdn) == trim($msisdnSesion) ) { //RVS 20171123
              	$dateIH = substr($generate->billPeriod, 0, 6) . "01 00:00:00";
	            		            	
	            	$period_format = format_date(strtotime($dateIH), 'monthonly'); //RVS 20171122
	              
	              $invoice_history['period'] = $period_format;
	              $invoice_history['contractId'] = $generate->contract;
	              $invoice_history['contractOfSendDetails'] = $_SESSION['sendDetail']['contractId'];
	              $invoice_history['invoiceId'] =  $generate->dei; //cambio solicitado RVS 20171124
	              $invoice_history['invoiceNumber'] = $generate->dei; //cambio solicitado RVS 20171124     	              
                $invoice_history['dueDate'] = format_date(strtotime($generate->expirationDate), 'longfactura'); //RVS 20171122  
               
	              $status = 'Pagada';
                if(strtoupper($generate->invoiceStatus) != 'PAGADA'){

                      if (strtoupper($generate->invoiceStatus) == 'VENCIDA' ) { 

                        $status = 'Vencida';

                      }else if( strtoupper($generate->invoiceStatus) == 'PENDIENTE'){

                        $status = 'Pendiente';

                      }else if(date("Y-m-d-H:m") > $generate->expirationDate){

                        $status = 'Vencida';

                      }else if(date("Y-m-d-H:m") <= $generate->expirationDate){

                        $status = 'Pendiente';
                      }
                  }else{
                    $status = 'Pagada';
                  } 
                    
                  
	              $invoice_history['status'] = $status;
	              $invoice_history['invoiceAmount'] = $this->tbo_config->formatCurrency($generate->invoiceAmount);
	              array_push($dataresponse, $invoice_history);
              }
              
            }
          }
          return $dataresponse;
        }
      }
    }
    catch (\Exception $e) {
      //return message in rest
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

    $factura = isset($_SESSION['sendDetail']['invoiceId']) ? $_SESSION['sendDetail']['invoiceId'] : '';
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
