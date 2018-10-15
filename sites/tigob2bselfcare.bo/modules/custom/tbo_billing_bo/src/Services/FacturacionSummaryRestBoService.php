<?php

namespace Drupal\tbo_billing_bo\Services;

use Drupal\tbo_billing\Services\BillingSummaryService;
use Drupal\adf_core\Util\UtilArray;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class FacturacionSummaryRestBoService.
 *
 * @package Drupal\tbo_billing_bo\Services
 */
class FacturacionSummaryRestBoService extends BillingSummaryService {


  /**
   * Constructor.
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
   * @return ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = $this->getSummary($_GET['type'],$_GET['client']);

    return new ResourceResponse($response);
  }


  /**
   *
   */
  public function getSummary($type, $client_code) {
    #ESTE VALOR SE TOMARÁ DE SESION DE CLIENTID
    $clientid = $client_code;

    # Obtener las configuracion de los parámetros establecidos para usar en el servicio
    $config = \Drupal::config("tbo_billing_bo.bill_payment_settings");
	  $group = "visualizacion";

    #inicializar variables para contabilizar los resultados
	  $invoices = [];  
    $ammount=0;
    $facturas=0;


    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params2['query'] = [
      'clientId' => $clientid,
      'offset' => $config->get($group)['servicedashboardparam']['billingoffsetdashboard'],
      'limit' => $config->get($group)['servicedashboardparam']['billinglimitdashboard'],
      'quantity' => $config->get($group)['servicedashboardparam']['billingquantitydashboard'],
    ];
    
    //PARÁMETROS
    $params2['tokens'] = [
      'clientId' => $clientid,
      'offset' =>$config->get($group)['servicedashboardparam']['billingoffsetdashboard'],
      'limit' => $config->get($group)['servicedashboardparam']['billinglimitdashboard'],
      'quantity' => $config->get($group)['servicedashboardparam']['billingquantitydashboard'],
    ];
    
    try {
      $response2 = $this->api->getFacturacionInformationInicio($params2); //llamada al WS //Aca llamariamos al servicio pero SIN el contractNumber, tendriamos que definir una copia de este servicio que NO tenga ese parametro
		  if(!empty($response2->error)){
        $message = $response->error->message;
        throw new Exception($message);
      }

      
		  #if length is 1
      if(count($response2)==1){
        $ammount = $response2->debtAmount;
        $facturas=$response2->unpaidInvoiceCount; 
        
      }else{
        foreach ($response2 as $key) {
          $ammount = $ammount + $key->debtAmount;
          $facturas=$facturas + $key->unpaidInvoiceCount;       
          
        }
      }

    }catch (\Exception $e) {
      //return message in rest   
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    #setear valores totales
    if(isset($ammount) && $ammount != ''){
        $total_invoice['symbol'] = $this->tbo_config->formatCurrency($ammount); #agrega el simbolo moneda definido en conf
        $total_invoice['ammount'] = $ammount;   # Tltal deuda
        $total_invoice['facturas'] = $facturas; # Total facturas
    }else{
         $total_invoice['ammount'] ='';
         $total_invoice['facturas'] = '';
         drupal_set_message(t('An error occurred and processing did not complete.'), 'error'); 
    }
    
    array_push($invoices, $total_invoice); 

		$summary=$invoices;  
		return $summary; 

  }
}