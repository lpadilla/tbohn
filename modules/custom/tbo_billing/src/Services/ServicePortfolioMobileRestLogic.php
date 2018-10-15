<?php

namespace Drupal\tbo_billing\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class ServicePortfolioService.
 *
 * @package Drupal\tbo_billing\Services
 */
class ServicePortfolioMobileRestLogic implements ServicePortfolioServiceInterface {

  private $api;
  private $currentUser;

  /**
   * ServicePortfolioService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return ResourceResponse
   */
  public function get(\Drupal\Core\Session\AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    //Get client data
    $document_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    //Change document type
    switch (strtoupper($document_type)) {
      case 'NIT':
        $document_type = 'NT';
    }
     //?idType=nit&id=830509497&businessUnit=B2B&offset=1&limit=100
    // Parameters for service
    $params['query'] = [
      'idType' => $document_type,
      'id' => $document_number,
       'businessUnit' =>'',
       'offset' => $page,
        'limit' => $cant_element

    ];

    try {
      if (method_exists($this->api, 'GetLineDetailsbyDocumentId')) {
        $data = $this->api->GetLineDetailsbyDocumentId($params);
      }
      else {
        throw new \Exception('No se encuentra el servicio GetLineDetailsbyDocumentId', 500);
      }
    } catch (\Exception $e) {
          //return message in rest
      return new ResourceResponse(UtilMessage::getMessage($e));
    }
    $response = [];
    if (isset($data)) {
      $counter = 1;
      foreach ($data->lineCollection as $data_key => $data_value) {
        foreach ($data_value->offeringList as $key => $value) {
          //Format status
          $status = $value->status;
          if ($status == 'Active') {
            $status = 'Servicio activo';
            $status2 = 'Activo';
          }
          elseif ($status == 'Inactive') {
            $status = 'Inactivo';
            $status2 = 'Inactivo';
          }
          elseif ($status == 'Suspended for client request') {
            $status = 'Suspendido por solicitud del cliente';
            $status2 = 'Suspendido';
          }
          elseif ($status == 'Suspended limit consumption') {
            $status = 'Suspendido limite de Consumo';
            $status2 = 'Suspendido';
          }
          elseif ($status == 'Indebtedness') {
            $status = 'Suspendido por Deuda';
            $status2 = 'Suspendido';
          }

          if ($data_value->productId != 1) {
            $assoc = $value->Contract->streetAddress;
            $response[$assoc][] = [
              'service_contract' => $value->Contract->contractId,
              'service_status' => $status,
              'service_status2' => $status2,
              'service_plan' => $value->offeringName,
              'address' => $value->Contract->streetAddress,
              'category_name' => $data_value->productName,
              'productId' => $data_value->productId,
              'subscriptionNumber' => $value->subscriptionNumber,
              'counter' => $counter++,
							'service_type' => 'fijo',
            ];
          }
        }
      }
    }
    else {
      $response['error'] = 'En este momento no podemos obtener la información de tus servicios móviles';
    }

    return new ResourceResponse($response);
  }

}
