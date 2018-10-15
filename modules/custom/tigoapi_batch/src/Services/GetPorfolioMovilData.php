<?php

namespace Drupal\tigoapi_batch\Services;

use Drupal\tigoapi_batch\TigoapiBatchProcessInterface;

/**
 * Class GetPorfolioMovilData.
 *
 * @package Drupal\tboapi_batch
 */
class GetPorfolioMovilData implements TigoapiBatchProcessInterface {
  /**
   * @var configurationInstance\Drupal\tbo_billing\Plugin\Config\ServicePortfolioBlockClass
   */
  protected $configurationInstance;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  public function __construct() {
    // Store our dependency.
    $this->configurationInstance = \Drupal::service('tbo_billing.service_portfolio_block_logic_api_batch');
  }

  /**
   * @return string
   */
  public function getId() {
    return $this->configurationInstance->getId();
  }

  /**
   * @return int
   */
  public function getRowsBySteps() {
    return $this->configurationInstance->getRowsBySteps();
  }

  /**
   * @return int
   */
  public function getCacheTime() {
    return $this->configurationInstance->getCacheTime();
  }

  /**
   * @param $search_key
   * @return array
   *
   *   Se inicializa el llamado de datos buscando los numeros de contrato con el documentNumber y documentType
   */
  public function prepare($search_key) {
    return $this->configurationInstance->prepare($search_key);
  }

  /**
   * @param $row
   *   -> respuesta de prepare() separadas
   * @return array
   *
   *   Se utiliza la respuesta de prepare() para continuar con el llamado de los datos necesarios para el card
   */
  public function processStep($row) {
    return $this->configurationInstance->processStep($row);
  }

  /**
   * @param $data
   * @return string
   */
  public function result($data) {
    return $this->configurationInstance->result($data);
  }

}
