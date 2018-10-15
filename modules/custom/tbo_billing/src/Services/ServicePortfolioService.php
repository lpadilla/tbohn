<?php

namespace Drupal\tbo_billing\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class ServicePortfolioService.
 *
 * @package Drupal\tbo_billing\Services
 */
class ServicePortfolioService implements ServicePortfolioServiceInterface {

  private $api;
  private $tbo_config;
  private $currentUser;
  protected $segment;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
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
    $document_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    // Change document type.
    switch (strtoupper($document_type)) {
      case 'NIT':
        $document_type = 'NT';
    }

    // Parameters for service.
    $params['tokens'] = [
      'documentType' => $document_type,
      'documentNumber' => $document_number,
    ];
    try {
      if (method_exists($this->api, 'getByAccountUsingCustomer')) {
        $data = $this->api->getByAccountUsingCustomer($params);
      }
      else {
        throw new \Exception('No se encuentra el servicio getByAccountUsingCustomer', 500);
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
          'label' => 'Error ' . $segment_data['error']['code'] . ': ' . $segment_data['error']['message'],
        ],
      ]);*/

      if ($e->getCode() == Response::HTTP_NOT_FOUND) {
        // Todo ver con el personal de análisis que debería
        // Todo retornar en caso de que no encuentre los datos.
        $data = array();
      }
      else {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
    }
    $response = [];
    if (isset($data)) {
      $counter = 1;
      foreach ($data as $data_key => $data_value) {
        foreach ($data_value->offeringList as $key => $value) {
          // Format status.
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
							'measuringElement' => $value->measuringElement,
            ];
          }
        }
      }
    }
    else {
      $response['error'] = 'En este momento no podemos obtener la información de tus servicios fijos';
    }

    return new ResourceResponse($response);
  }

  /**
   * Responds to POST requests.
   * calls create method.
   *
   * @param $params
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $params) {
    $this->currentUser = $currentUser;
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $response = [];

    $detalles = t('Usuario ' . $service->getName() . ' hace consulta detallada en el portafolio de servicios asociados a su empresa con los siguientes datos: por ' . $params['exactSearch'] . ' y categoría ' . implode(',', $params['category']));

    if ($params['exactSearch'] == "") {
      $detalles = t('Usuario ' . $service->getName() . ' hace consulta detallada en el portafolio de servicios asociados a su empresa con los siguientes datos: categoría ' . implode(',', $params['category']));
    }

    if (count($params['category']) == 0) {
      $detalles = t('Usuario ' . $service->getName() . ' hace consulta detallada en el portafolio de servicios asociados a su empresa con los siguientes datos: por ' . $params['exactSearch']);
    }
    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => t('Servicios'),
      'description' => t('Usuario hace consulta especifica en portafolio de servicios'),
      'details' => $detalles,
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];

    // Save audit log.
    $service->insertGenericLog($data);

    return new ResourceResponse('Ok');
  }

}
