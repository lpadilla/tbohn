<?php

namespace Drupal\tbo_general_co\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\tbo_general\Services\CardBaseExampleService as CardBaseExampleServiceBase;

/**
 * Class CardBaseExampleService.
 *
 * @package Drupal\tbo_general\Services
 */
class CardBaseExampleService extends CardBaseExampleServiceBase {

  private $tbo_config;
  private $currentUser;

  /**
   * CurrentInvoiceService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   */
  public function __construct(TboConfigServiceInterface $tbo_config) {
    $this->tbo_config = $tbo_config;
  }

  /**
   *
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      // Get data invoice delivery.
      $data = $this->getData($_GET);
      if ($data) {
        return new ResourceResponse($data);
      }

    }
    catch (\Exception $e) {
      $response = new ResourceResponse();
      $response->setContent($e);
      $exception = [];
      foreach ($e->getMessage() as $error) {
        array_push($exception, $error);
      }
      \Drupal::logger("CurrentInvoiceRestResource")->notice("error al obtener el servicio", $exception);
      return $response;
    }

    return new ResourceResponse('error obteniendo los datos');
  }

  /**
   *
   */
  public function getData($params = []) {
    $response = [
      [
        'id' => 1,
        'company' => 'My company 1',
        'document_type' => 'Nit',
        'city' => 'Bogota',
      ],
      [
        'id' => 2,
        'company' => 'My company 2',
        'document_type' => 'CC',
        'city' => 'Medellin',
      ],
      [
        'id' => 3,
        'company' => 'My company 3',
        'document_type' => 'Nit',
        'city' => 'Bucaramanga',
      ],
      [
        'id' => 4,
        'company' => 'My company 4',
        'document_type' => 'Nit',
        'city' => 'Barraquilla',
      ],
      [
        'id' => 5,
        'company' => 'company 5',
        'document_type' => 'CC',
        'city' => 'Cali',
      ],
      [
        'id' => 6,
        'company' => 'company 6',
        'document_type' => 'Nit',
        'city' => 'Bogota',
      ],
      [
        'id' => 7,
        'company' => 'company 7',
        'document_type' => 'Nit',
        'city' => 'Pereira',
      ],
      [
        'id' => 8,
        'company' => 'company 8',
        'document_type' => 'CC',
        'city' => 'Medellin',
      ],
      [
        'id' => 9,
        'company' => 'company 9',
        'document_type' => 'Nit',
        'city' => 'Cali',
      ],
    ];

    return $response;
  }

}
