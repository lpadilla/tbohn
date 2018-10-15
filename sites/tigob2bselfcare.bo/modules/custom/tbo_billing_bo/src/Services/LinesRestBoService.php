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
 * Class LinesRestBoService.
 *
 * @package Drupal\tbo_billing_bo\Services
 */
class LinesRestBoService extends InvoiceHistoryRestService {

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
	public function get() {
    //NOTA IMPORTATNTE: CUANDO SE CAMBIE ESTE FUNCION DEBE CAMBIAR LA MMISMA FUNCION EN EL ARCHIVO UBICADO EN LA CARPETA DE PLUGIN>REST>RESOURCE QUE SE LLAMA "LINEASBORESTRESOURCES.PHP"
    \Drupal::service('page_cache_kill_switch')->trigger();
    $invoices = [];

    $contract = $_SESSION['contract']['number'];
    $cant_line=$_SESSION['contract']['lines'];
    $deuda    =$_SESSION['contract']['deuda'];
    $num_contract_client = $_GET['num_contract_client'];

    $nit = $_SESSION['company']['nit'];
    $docType = $_SESSION['company']['docType'];

    if($docType == 'nit'){
      $docType = 'ruc';
    }

    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params['query'] = [      
      'offset' => 1,
      'limit'=> 1200,
    ];
    
    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params['tokens'] = [
      'nit' => $nit,
      'docType'=> $docType, 
      'offset' => 1,
      'limit'=> $_GET['limit_lines'], 
    ];
  
    $response=$this->api->getCustomerLinesByCustomerId($params);  
    $result=[];    
    $lienasinfo=[];
    $lin=0;
    
    if($_SESSION['num_contracts_line'] < 2){
      $response=$response[0];
      $lienasinfo['datos']=$response->accounts->AssetType;
          
      $result['deuda']=$deuda;
      $result['contrato']=$contract;
      $result['cant']=$cant_line;      

    }else{
      foreach ($response as $key) {
        if($key->contractNumber == $contract){                  
         if(isset($key->accounts->AssetType) && !empty($key->accounts->AssetType)){
            $lienasinfo['datos']=$key->accounts->AssetType;
            $lienasinfo['empty'] = 0;
          }else{
            $lienasinfo['empty'] = 1;
            $lienasinfo['datos'] = [];
          }

          $result['deuda']=$deuda;
          $result['contrato']=$contract;
          $result['cant']=$cant_line;         
          break; 
        }
      }
    }
    
    $lineasResul = [];
    $lineasAr = [];
    
    if($lienasinfo['empty'] == 0){
      if($result['cant'] < 2){
        $lineasAr['msisdn'] = $lienasinfo['datos']->msisdn;
        $lineasAr['plans']['PlanType']['planName'] = $lienasinfo['datos']->plans->PlanType->planName;
        $lineasAr['l']=0;
        array_push($lineasResul, $lineasAr);
      }else{
        $x=0;
        foreach ($lienasinfo['datos'] as $keys => $line) {
          $lineasAr['msisdn'] = $line->msisdn;
          $lineasAr['plans']['PlanType']['planName'] = $line->plans->PlanType->planName;
          $lineasAr['l']=$x++;
          
          array_push($lineasResul, $lineasAr);
        }
      }
    }

    
    $result['datos'] = $lineasResul; 
    $_SESSION['lineas_data'] = null;   
    $_SESSION['lineas_data']=$result;  
    return $result;
  }

}
