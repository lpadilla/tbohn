<?php

namespace Drupal\tbo_account_co;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class TboAccountCoServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override Apiclient.
    $definition = $container->getDefinition('tbo_account.create_account');
    $definition->setClass('Drupal\tbo_account_co\Services\CreateAccountServiceCo');
  }

}
