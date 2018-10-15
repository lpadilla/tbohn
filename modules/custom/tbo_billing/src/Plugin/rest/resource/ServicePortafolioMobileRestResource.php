<?php

namespace Drupal\tbo_billing\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "service_portfolio_mobile_rest_resource",
 *   label = @Translation("Service portfolio mobile rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo_billing/rest/service-portfolio-mobile",
 *      "https://www.drupal.org/link-relations/create" = "/tbo_billing/rest/service-portfolio-mobile"
 *   }
 * )
 */
class ServicePortafolioMobileRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('tbo_general'),
      $container->get('current_user')
    );
  }

  /**
   * @param $data
   *
   * @return mixed
   */
  public function get() {

    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('tbo_billing.service_portfolio_mobile_rest_logic')->get();

  }
}