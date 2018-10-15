<?php

namespace Drupal\tbo_services\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class DetalleVerProductoFijoService
 *
 * @package Drupal\tbo_services
 */
class DetalleVerProductoFijoService {
  
  private $tbo_config;
  protected $api;
  protected $clientId;
  protected $company_document;

  /**
   * Constructor.
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
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  
    $get_data = isset($_GET['isData'])?isset($_GET['isData']):'';
    $detail_log = isset($_GET['detailLog'])?isset($_GET['detailLog']):'';
    $last_data = $_SESSION['serviceDetail'];
  
    $params = [
      'tokens' => [
        'contractId' => $last_data['contractId'],
        'productNumber' => $last_data['productId'],
        'suscriptionNumber' => $last_data['subscriptionNumber'],
      ],
    ];

    // In mobile no exception - Service no found.
    if ($last_data['serviceType'] === 'movil') {
      $params['no_exception'] = TRUE;
    }

    try {
      $details = $this->api->getByAccountDataUsingContract($params);
    }
    catch (\Exception $e) {
      //return message in rest
      return new ResourceResponse(UtilMessage::getMessage($e));
    }
  
    $response =  [
      'card' => $last_data,
      'details' => $details,
    ];
  
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
  
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => t('Servicios'),
      'description' => t('Usuario consulta detalle de producto fijo'),
      'details' => t('Usuario ' . $service->getName() . ' consulto el detalle del servicio ' . $last_data['category'] . ' fijo del contrato ' . $last_data['contractId'] . ' de la dirección ' . $last_data['address']),
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];
  
    if($get_data) {
      $service->insertGenericLog($data);
      return (new ResourceResponse($response))->addCacheableDependency($build);
    }
  
    if($detail_log) {
      $data['description'] = t('Usuario consulta detalle del contrato de producto fijo');
      $data['details'] = t('Usuario ' . $service->getName() . ' consulto el detalle del contrato ' . $last_data['contractId'] . ' del servicio ' . $last_data['category'] . ' fijo de la dirección ' . $last_data['address']);
      $service->insertGenericLog($data);
      return (new ResourceResponse('OK'))->addCacheableDependency($build);
    }
  
  }

  
}