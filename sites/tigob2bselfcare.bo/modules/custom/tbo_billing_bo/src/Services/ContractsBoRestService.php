<?php

namespace Drupal\tbo_billing_bo\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_core\Base\BaseApiCache;

/**
 * Class ContractsBoRestService.
 *
 * @package Drupal\tbo_billing
 */
class ContractsBoRestService {
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
    
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $client_code = $_GET['client']; # client_code a enviar al servicio
    $total_contracts= $_GET['total_contracts'];

    $response = $this->getContracts($_GET['type'],$_GET['cant_contratos'],$client_code,$total_contracts);    
    return new ResourceResponse($response);
  }


  public function getContracts($type,$cantContracts,$client_code,$total_contracts) {
		#ESTE VALOR SE TOMARÁ DE SESION DE CLIENTID
   
    $_SESSION['contracts_data_line']= [];

    $cantInvoice=0;
    $clientid = $client_code;

    $params['query'] = [
      'client_id' => $clientid,
    ];
    
    $params['tokens'] = [
      'client_id' => $clientid,     
    ];

    $invoices = [];

    try {

    	$response=$this->api->getContractsMobile($params); // llamada a WS que trae los contratos!!!
      if(!empty($response->fault)){
        $message = $response->fault->detail;
        throw new Exception($message);

      }
      if(!empty($response->error)){
        $message = $response->error->message;
        throw new Exception($message);
      }

      if($_SESSION['ciclo_contratos']=="")
        $_SESSION['ciclo_contratos']=1;

      

	    if($_SESSION['num_contracts']==""){
        $_SESSION['num_contracts']=0;
      }
	    
	    
  	    foreach ($response->contract as $key) {
  	      $cantInvoice = $cantInvoice +1;
  	      if($cantInvoice<=$cantContracts){
  	        $invoice['flag']     = $cantInvoice+$_SESSION['num_contracts'];
  	        $invoice['contract'] = $key->id;
  	        $invoice['lineas']   = $key->countMsisdn;
  					
  			  $ammount = 0;
  	        
  	        array_push($invoices, $invoice);
  	        
  	      }
  	     

  	    }


      $_SESSION['num_contracts']=$_SESSION['num_contracts']+$cantInvoice; #colocar en sesion el numero de contratos que trae del servicio
      $_SESSION['ciclo_contratos']+=1;
    
    } catch (\Exception $e) {
      //return message in rest
      $message = "Catch en ContractsBoBlock: " . UtilMessage::getMessage($e);
			\Drupal::logger('TBO_Billing')->error($message);
      return new ResourceResponse(UtilMessage::getMessage($e));
      
    } finally {
			# Despues de la ejecucion del servicio de conocer los contratos, se configura la llamada de deuda por contrato
      $config = \Drupal::config("tbo_billing_bo.bill_payment_settings");
	  	$group = "visualizacion";
      
	    $params2['query'] = [
	      'clientId' => $clientid,
	      'offset' => $config->get($group)['servicedashboardparam']['billingoffsetdashboard'],
	      'limit' => $config->get($group)['servicedashboardparam']['billinglimitdashboard'],
	      'quantity' => $config->get($group)['servicedashboardparam']['billingquantitydashboard'],
	    ];
	    
	    //PARÁMETROS
	    $params2['tokens'] = [
	      'clientId' => $clientid,
	      'offset' => $config->get($group)['servicedashboardparam']['billingoffsetdashboard'],
	      'limit' => $config->get($group)['servicedashboardparam']['billinglimitdashboard'],
	      'quantity' => $config->get($group)['servicedashboardparam']['billingquantitydashboard'],
	    ];
     
      $response2 = $this->api->getFacturacionInformationInicio($params2); //llamada al WS
      
      #Si cantidad de valores que trae el servicio es 1
      if(count($response2)==1){
        foreach ($invoices as $key2 => $val2) {
          if(isset($response2->billingAccountId) ){
	          if($response2->billingAccountId == $val2['contract']){
	            $invoices[$key2]['deuda']=$this->tbo_config->formatCurrency($response2->debtAmount);
	            $invoices[$key2]['num_contract']=$cantInvoice;
	            break;
	          }         
        	}
        }
      }else{ #mas  de un resultado de sumatoria de facturas
        foreach ($response2 as $key) {
          foreach ($invoices as $key2 => $val2) {
            if($key->billingAccountId == $val2['contract']){
              $invoices[$key2]['deuda']=$this->tbo_config->formatCurrency($key->debtAmount);
              $invoices[$key2]['num_contract']=$cantInvoice;
              break;
            }         
          }
             
        }
      }

	 }


    if($_SESSION['contracts_data']=="")
      $_SESSION['contracts_data'] = $invoices; # Informacion de contratos en sesion para ser usados en lineas
    else
       $_SESSION['contracts_data'] =array_merge($_SESSION['contracts_data'],$invoices);


    # informacion de los contratos con su deuda en sesion, para ser usada en el C.U Consulta de saldo
     $_SESSION['contracts_data_line']=$_SESSION['contracts_data'];
     $_SESSION['num_contracts_line']=$_SESSION['num_contracts'];

    $contracts = $invoices;

		return $invoices;

  }
}