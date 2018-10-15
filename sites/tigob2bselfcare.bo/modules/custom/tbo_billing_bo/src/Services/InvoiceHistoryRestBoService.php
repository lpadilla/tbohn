<?php

namespace Drupal\tbo_billing_bo\Services;

use Drupal\tbo_billing\Services\InvoiceHistoryRestService;
use Drupal\adf_core\Util\UtilArray;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class InvoiceHistoryRestBoService.
 *
 * @package Drupal\tbo_billing_bo\Services
 */
class InvoiceHistoryRestBoService extends InvoiceHistoryRestService {

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
      $contractId = $_SESSION['sendDetail']['contractId'];      
      $msisdnSesion = $_SESSION['sendDetail']['invoice']['msisdn']; 
      $client_code = false;
      $client_code = $_SESSION['sendDetail']['client_code_for_detail'];
      $clientId    = $_SESSION['company']['nit'];

      $config = \Drupal::config("tbo_billing_bo.bill_payment_settings");
	    $group = "visualizacion";
	   
	    if($_SESSION['casos_borde']!=null && $_SESSION['casos_borde']!=""){
	    	$params['query'] = [
	      'clientruc' => $clientId,
	      'contractNumber' => $contractId,
	      'offset' => $config->get($group)['servicehistoryparam']['billingoffsethistory'],
	      'limit' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
	      
	    ];
	    
	    $params['tokens'] = [
	      'clientruc' => $clientId,
	      'contractNumber' => $contractId,
	      'offset' => $config->get($group)['servicehistoryparam']['billingoffsethistory'],
	      'limit' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
	      
	     ];

	    }else{
	    	$params['query'] = [
		      'clientruc' => $clientId,
		      'contractNumber' => $contractId,
		      'quantity' => $config->get($group)['servicehistoryparam']['billingquantityhistory']*2,
		      'offset' => $config->get($group)['servicehistoryparam']['billingoffsethistory'],
		      'limit' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
		      
		    ];
		    
		    $params['tokens'] = [
		      'clientruc' => $clientId,
		      'contractNumber' => $contractId,
		      'quantity' => $config->get($group)['servicehistoryparam']['billingquantityhistory']*2,
		      'offset' => $config->get($group)['servicehistoryparam']['billingoffsethistory'],
		      'limit' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
		      
		    ];
	    }

	    
   
	    $account_contract = [];
	    $data_filters_address = [];

	    $account_data = [];
	    $data = [];

	    try {
	    	if($_SESSION['casos_borde']!=null && $_SESSION['casos_borde']!=""){
	    		$response = $this->api->findCustomerBillsByRucWithContractNumber($params);
	    	}else{
	    		$response = $this->api->findCustomerBillsByRucWithContractNumberOne($params);
	    	}
	        

	    } catch (\Exception $e) {
	        //return message in rest
	        return new ResourceResponse(UtilMessage::getMessage($e));
	    }

      //Save Audit log
      $this->saveAuditLog();
	
		if ($response){
				
				$for_data = $response->invoicesCollection;

				$dataresponse = [];
	        foreach ($for_data as $invoice){
	            $msisdn = '000000';
	            $invoice_history = [];
	          	$dateIH = substr($invoice->billPeriod, 0, 10) . " 00:00:00"; 
	          	$period_format = format_date(strtotime($dateIH), 'monthonly'); 
	            
	            $invoice_history['period'] = $period_format;
	            $invoice_history['contractId'] = $invoice->contract;
	            
	            $invoice_history['contractOfSendDetails'] = $invoice->invoiceNumber;
	            
	            $invoice_history['invoiceId'] =  $invoice->invoiceNumber;
	            $invoice_history['invoiceNumber'] = $invoice->invoiceNumber;
	            
	            $invoice_history['dueDate'] = format_date(strtotime(substr($invoice->expirationDate, 0, 10)), 'longfactura'); 
	            
	            $status = 'Pagada';
	            if (strtoupper($invoice->invoiceStatus) != 'CA' && date("Y-m-d-H:m") > $invoice->expirationDate) {
	              $status = 'Vencida';
	            }
	            if (strtoupper($invoice->invoiceStatus) != 'CA' && date("Y-m-d-H:m") <= $invoice->expirationDate) {
	              $status = 'Pendiente de Pago';
	            }
	            $invoice_history['status'] = $status;
	            $invoice_history['invoiceAmount'] = $this->tbo_config->formatCurrency($invoice->invoiceAmount);
	            array_push($dataresponse, $invoice_history);
	        }
	      return $dataresponse;
	    }
      
    }
    catch (\Exception $e) {
      //return message in rest
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    return FALSE;

  }

}
