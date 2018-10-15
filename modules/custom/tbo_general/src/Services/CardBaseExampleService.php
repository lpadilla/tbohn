<?php

namespace Drupal\tbo_general\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CardBaseExampleService.
 *
 * @package Drupal\tbo_general\Services
 */
class CardBaseExampleService implements CardBaseExampleServiceInterface {

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
        'company' => 'company 1',
        'document_type' => 'Nit',
      ],
      [
        'id' => 2,
        'company' => 'company 2',
        'document_type' => 'CC',
      ],
      [
        'id' => 3,
        'company' => 'company 3',
        'document_type' => 'Nit',
      ],
      [
        'id' => 4,
        'company' => 'company 4',
        'document_type' => 'Nit',
      ],
      [
        'id' => 5,
        'company' => 'company 5',
        'document_type' => 'CC',
      ],
      [
        'id' => 6,
        'company' => 'company 6',
        'document_type' => 'Nit',
      ],
      [
        'id' => 7,
        'company' => 'company 7',
        'document_type' => 'Nit',
      ],
      [
        'id' => 8,
        'company' => 'company 8',
        'document_type' => 'CC',
      ],
      [
        'id' => 9,
        'company' => 'company 9',
        'document_type' => 'Nit',
      ],
    ];

    return $response;
  }

}
