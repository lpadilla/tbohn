<?php

namespace Drupal\tbo_emulate_bo\Plugin\Config\Block;

use Drupal\tbo_emulate_bo\Plugin\Block\UnmasqueradeBlock;

/**
 * Manage config a 'UnmasqueradeBlockClass' block.
 */
class UnmasqueradeBlockClass {

  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_emulate_bo\Plugin\Block\UnmasqueradeBlock $instance
   * @param $config
   */
  public function setConfig(UnmasqueradeBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function build(UnmasqueradeBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    \Drupal::service('page_cache_kill_switch')->trigger();
    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'unmasquerade_bo',
    ];

    $others = [
      '#role' => $_SESSION['emular_role'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    return $this->instance->getValue('build');
  }

}
