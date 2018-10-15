<?php
namespace Drupal\tbo_api_hn;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class TboApiHnServiceProvider extends ServiceProviderBase {

    /**
     * {@inheritdoc}
     */
    public function alter(ContainerBuilder $container) {
        //Override Apiclient
        $definition = $container->getDefinition('tbo_api.client');
        $definition->setClass('Drupal\tbo_api_hn\TboApiHnClient');
    }
}