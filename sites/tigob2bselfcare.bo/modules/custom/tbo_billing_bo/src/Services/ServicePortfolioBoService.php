<?php

namespace Drupal\tbo_billing_bo\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api_bo\TboApiBoClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class ServicePortfolioService.
 *
 * @package Drupal\tbo_billing\Services
 */
class ServicePortfolioBoService implements ServicePortfolioBoServiceInterface {

  private $api;
  private $tbo_config;
  private $currentUser;
  protected $segment;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api_bo\TboApiBoClient $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiBoClient $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Get client data.
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
    $docType = $_SESSION['company']['docType'];
    if($docType == 'nit'){
      $docType = 'ruc';
    }

    // Parameters for service.
    $params['tokens'] = [
      'documentNumber' => $document_number,
      'docType' => $docType,
    ];

    $params['query'] =  [      
      'offset' => '1',
      'limit' => '800',
    ];

    

    try {
      if (method_exists($this->api, 'getCustomerGeneralInfoByCustomerId')) {
        $data = $this->api->getCustomerGeneralInfoByCustomerId($params);
       
      }
      else {
        throw new \Exception('No se encuentra el servicio getCustomerGeneralInfoByCustomerId', 500);
      }
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }
    $contracts = $data->Envelope->Body->getClientAccountGeneralInfoMobileResponse->contracts;
    $contract_lines = $line = $account = [];
    foreach ($contracts->contract as $contract){
        $accounts = [];
        if (count(get_object_vars($contract->accounts->AssetType)) < 1) { //Este count devuelve cero para los que tienen varios accounts
          foreach ($contract->accounts->AssetType as $account){
            
            $aStatus = $this->determinateStatus($account->accountState);
            
            array_push($accounts,array('msisdn'=>$account->msisdn,
                                                'contract'=> $contract->contractNumber,
                                                'service_status'=>$aStatus[0],
                                                'service_status2'=>$aStatus[1],
                                                'category_name' => 'Telefonía móvil',
                                                'service_plan'=>$account->plans->PlanType->planName));
          }
        }
        else {
            
            $aStatus = $this->determinateStatus($contract->accounts->AssetType->accountState);
            
            array_push($accounts,array('msisdn'=>$contract->accounts->AssetType->msisdn,
                                                'contract'=> $contract->contractNumber,
                                                'service_status'=>$aStatus[0],
                                                'service_status2'=>$aStatus[1],
                                                'category_name' => 'Telefonía móvil',
                                                'service_plan'=>$contract->accounts->AssetType->plans->PlanType->planName));
        }
      $line = array($contract->contractNumber => $accounts);
      array_push($contract_lines, $line);
     
    }
    
    return new ResourceResponse($contract_lines);
    
  }
  
  public function determinateStatus($status){
  
  	$aStatus = [];
  
	  if ($status == 'AC') {
	    $status = t('Servicio activo');
	    $status2 = t('Activo');
	  }
	  
	  elseif ($status == 'MO') {
	    $status = t('Corte por Mora');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'SR') {
	    $status = t('Corte Saliente por deuda');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'LI') {
	    $status = t('Corte Saliente por Limite');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'LL') {
	    $status = t('Corte saliente por limite (larga distancia)');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'SP') {
	    $status = t('Corte Saliente a pedido');
	    $status2 = t('Suspendido');
	  }
	  else {
	    $status = t('No Activo');
	    $status2 = t('Inactivo');
	  }
	
		$aStatus[]=$status;
		$aStatus[]=$status2;
	
		return $aStatus;
	}
  
}
