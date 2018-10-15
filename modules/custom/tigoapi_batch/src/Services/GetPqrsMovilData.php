<?php

namespace Drupal\tigoapi_batch\Services;

use Drupal\tigoapi_batch\TigoapiBatchProcessInterface;

/**
 * Class GetPqrsMovilData.
 *
 * @package Drupal\tboapi_batch
 */
class GetPqrsMovilData implements TigoapiBatchProcessInterface {

  /**
   * @var \Drupal\tbo_services\Services\Batch\QueryPqrsServiceLogicBatch
   */
  protected $configurationInstance;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    // Store our dependency.
    $this->configurationInstance = \Drupal::service('tbo_billing.query_pqrs_logic_api_batch');
  }

  /**
   * @return string
   *   The configuration id.
   */
  public function getId() {
    return $this->configurationInstance->getId();
  }

  /**
   * Implements getRowsBySteps().
   *
   * @return int
   *   The rows by step.
   */
  public function getRowsBySteps() {
    return $this->configurationInstance->getRowsBySteps();
  }

  /**
   * Implements getCacheTime().
   *
   * @return int
   *   The cache time.
   */
  public function getCacheTime() {
    return $this->configurationInstance->getCacheTime();
  }

  /**
   * Implements prepare().
   *
   * @param $search_key
   *   The search key.
   *
   * @return array
   */
  public function prepare($search_key) {
    return $this->configurationInstance->prepare($search_key);
  }

  /**
   * Implements processStep().
   *
   * @param $row
   *   respuesta de prepare() separadas
   *
   * @return array
   *   Return process step.
   */
  public function processStep($row) {
    return $this->configurationInstance->processStep($row);
  }

  /**
   * Implements result().
   *
   * @param $data
   *   The result data.
   *
   * @return string
   *   Return data.
   */
  public function result($data) {
    return $this->configurationInstance->result($data);
  }

}
