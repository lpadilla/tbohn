<?php

namespace Drupal\tbo_billing\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class ServicePortfolioServiceBatch.
 *
 * @package Drupal\tbo_billing\Services
 */
class ServicePortfolioServiceBatch implements ServicePortfolioServiceBatchInterface {

  private $api;
  private $tboConfig;
  private $segment;
  private $currentUser;
  private $limit = 100;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Thererere.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   The service object.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api, AccountInterface $currentUser) {
    $this->tboConfig = $tboConfig;
    $this->api = $api;
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
    $this->currentUser = $currentUser;
  }

  /**
   * Get id.
   *
   * @return string
   *   The id value.
   */
  public function getId() {
    return 'get_portfolio_movil_data';
  }

  /**
   * Get row by step.
   *
   * @return int
   *   The row.
   */
  public function getRowsBySteps() {
    return 1;
  }

  /**
   * Get time in cache.
   *
   * @return int
   *   Time
   */
  public function getCacheTime() {
    return time() + 3600;
  }

  /**
   * Se inicializa el llamado de datos buscando los numeros de contrato.
   *
   * @param array $search_key
   *   The search key.
   *
   * @return array
   *   The array values.
   */
  public function prepare(array $search_key) {
    $init = 1;
    $response = [];
    $end = FALSE;
    $pos = 0;
    $idType = strtolower($search_key['document_type']);
    $id = (array_key_exists('document_number', $search_key)) ? $search_key['document_number'] : $search_key['key'];
    while (!$end){
      $params['query'] = [
        'idType' => $idType,
        'id' => $id,
        'businessUnit' => 'B2B',
        'offset' => $init ,
        'limit' => $this->limit,
      ];
      $result = $this->getServiceData($params);
      $cantData = count($result);
      if($cantData > 0){
        $response[$pos]['key'] = $search_key['key'];
        $response[$pos]['key_data'] = $search_key['key'];
        $response[$pos]['document_type'] = $search_key['document_type'];
        $response[$pos]['init'] = $init;
        $response[$pos]['end'] = $this->limit;

        $init += $this->limit;
      }
      if ($cantData == 0 || $cantData < $this->limit) {
        $end = TRUE;
      }
      $pos++;
    }

    return $response;
  }

  /**
   * Llamado de los datos necesarios para el card.
   *
   * @param array $row
   *   Respuesta de prepare() separadas.
   *
   * @return array
   *   array.
   */
  public function processStep(array $row) {
    $response =[];
    $params['query'] = [
      'idType' => strtolower($row['document_type']),
      'id' => $row['key'],
      'businessUnit' => 'B2B',
      'offset' => $row['init'],
      'limit' => $row['end'],
    ];

   $data = $this->getServiceData($params);
    $counter = 1;
    foreach ($data as $line) {

      $service = "No Disponible";
      $service2 = "No Disponible";
      $plan = $line->plan->planDescription;
      $msisdn = $line->msisdn;
      $contract = $line->csn;

      if ($line->status != "" && $line->status != NULL) {

        if ($line->status == 'ACTIVE') {
          $service = 'Servicio activo';
          $service2 = 'Activo';
        }
        elseif ($line->status == 'FRAUD') {
          $service = 'Suspendido por Fraude';
          $service2 = 'Suspendido';
        }
        elseif ($line->status == 'THEFT_LOSS') {
          $service = 'Suspendido por Perdida';
          $service2 = 'Suspendido';
        }
        elseif ($line->status == 'INDEBTEDNESS') {
          $service = 'Suspendido por Deuda';
          $service2 = 'Suspendido';
        }
        elseif ($line->status == 'SUSPENDED_LIMIT_CONSUMPTION') {
          $service = 'Suspendido Limite de Consumo';
          $service2 = 'Suspendido';
        }
        elseif ($line->status == 'TEMPORAL_SUSPENDED_CLIENT') {
          $service = 'Suspendido Temporal por Cliente';
          $service2 = 'Suspendido';
        }
      }

      $response[] = [
        'group' => $msisdn,
        'msisdn' => $msisdn,
        'category_name' => 'Telefonía móvil',
        'service_contract' => $contract,
        'service_status' => $service,
        'service_status2' => $service2,
        'service_plan' => $plan,
        'productId' => -1,
        'subscriptionNumber' => -1,
        'counter' => $counter++,
        'service_type' => 'movil',
      ];
    }

    return $response;
  }

  /**
   * Get result.
   *
   * @param array $data
   *   Array data.
   *
   * @return array
   *   Array data.
   */
  public function result(array $data) {
    $response = [];

    foreach ($data as $data_key => $data_value) {
      foreach ($data_value as $key => $value) {
        $response[$value['msisdn']][] = $value;
      }
    }

    return $response;
  }

  private function getServiceData($params) {
    $result = [];
    try {
      $data = $this->api->GetLineDetailsbyDocumentId($params);
     if(isset($data->lineCollection)) {
       $result = $data->lineCollection;
     }
    }
    catch (\Exception $e) {
       // Send exception to segment.
       // Se comenta "Pendiente borrar" segun solicitud de Millicom ya que se reemplaza por el No 71.
      /*$segment_data = json_decode($e->getMessage(), TRUE);
      $uid = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
      $this->segment->track([
        'event' => 'TBO - Excepción',
        'userId' => $uid,
        'properties' => [
          'category' => 'Portafolio de Servicios',
          'label' => 'Error: ' . $segment_data['fault']['detail']['errorcode'],
        ],
      ]);*/

    }

    return $result;
  }

}
