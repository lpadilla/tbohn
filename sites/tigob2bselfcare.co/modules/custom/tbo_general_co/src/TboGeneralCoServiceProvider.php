<?php

namespace Drupal\tbo_general_co;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class TboGeneralCoServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override Config block card example
    // $config_card_example = $container->getDefinition('tbo_general.card_base_example_block');
    // $config_card_example->setClass('Drupal\tbo_general_co\Plugin\Config\CardBaseExampleBlockClass');
    // Override Config block card example logic
    // $config_card_example_logic = $container->getDefinition('tbo_general.card_base_example_block_logic');
    // $config_card_example_logic->setClass('Drupal\tbo_general_co\Services\CardBaseExampleService');.
  }

}
