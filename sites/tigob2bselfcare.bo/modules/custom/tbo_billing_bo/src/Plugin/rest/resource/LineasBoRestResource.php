<?php
namespace Drupal\tbo_billing_bo\Plugin\rest\resource;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\user\Entity\User;
use Drupal\masquerade\Masquerade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Utility\Token;
use Drupal\user\UserInterface;
use Drupal\tbo_api_bo\TboApiBoClient;
use Drupal\tbo_general\Services\TboConfigServiceInterface;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "lineas_bo_rest_resource",
 *   label = @Translation("Lineas Bo Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/billing/lineas_bo"
 *   }
 * )
 */
class LineasBoRestResource extends ResourceBase {
  /**
   * A current user instance..
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
	protected $tboApiBoClient;
	protected $currentUser;

	/**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
	public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,TboApiBoClient $tbo_api_bo_client) {
    	
    	parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    	$this->currentUser = $current_user; 
    	$this->tboApiBoClient = $tbo_api_bo_client;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('tbo_billing_bo'),
      $container->get('current_user'),
      $container->get('tbo_api_bo.client')
    );
  }


  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {

    //NOTA IMPORTATNTE: CUANDO SE CAMBIE ESTE FUNCION DEBE CAMBIAR LA MMISMA FUNCION EN EL ARCHIVO UBICADO EN LA CARPETA DE SERVICIOS QUE SE LLAMA "LINESRESTBOSERVICE.PHP"
    \Drupal::service('page_cache_kill_switch')->trigger();
    $invoices = [];

    $contract = $_SESSION['contract']['number'];
    $cant_line=$_SESSION['contract']['lines'];
    $deuda    =$_SESSION['contract']['deuda'];
    $num_contract_client = $_GET['num_contract_client']; 

    #ESTE VALOR SE TOMARÁ DE SESION DE CLIENTID
    

		$nit = $_SESSION['company']['nit'];
    $docType = $_SESSION['company']['docType'];

    if($docType == 'nit'){
      $docType = 'ruc';
    }


    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params['query'] = [    
      'offset' => 1,
      'limit'=> $_GET['limit_lines'],
    ];
    
    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params['tokens'] = [
      'nit' => $nit,
      'docType'=> $docType, 
      'offset' => 1,
      'limit'=> $_GET['limit_lines'], 
    ];


    $response=$this->tboApiBoClient->getCustomerLinesByCustomerId($params);
    $result=[];
    
    $lienasinfo=[];
    $lin=0;

    if($_SESSION['num_contracts_line'] < 2){
      $response=$response[0];
      $lienasinfo['datos']=$response->accounts->AssetType;
          
      $result['deuda']=$deuda;
      $result['contrato']=$contract;
      $result['cant']=$cant_line;
      //$result['num_con']=$num_contract_client;

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
          //$result['num_con']=$num_contract_client;
          
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
    
    return new ResourceResponse($result);

  }
}