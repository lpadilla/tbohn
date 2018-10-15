<?php

namespace Drupal\tbo_billing\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "export_portfolio_rest_resource",
 *   label = @Translation("Export portfolio rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/billing/portfolio/export",
 *     "https://www.drupal.org/link-relations/create" = "/tboapi/billing/portfolio/export"
 *   }
 * )
 */
class ExportPortfolioRestResource extends ResourceBase {

  protected $currentUser;

  /**
   * Implement of constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Pplugin_id.
   * @param mixed $plugin_definition
   *   Plugin_definition.
   * @param array $serializer_formats
   *   Serializer_formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
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
    $container->get('logger.factory')->get('tbo_billing')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('tbo_billing.export_portfolio_rest_logic')->get();
  }

  /**
   * Implement of post.
   *
   * @return array|\Drupal\rest\ModifiedResourceResponse|\Drupal\rest\ResourceResponse
   *   Resultado del llamado al metodo.
   */
  public function post() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('tbo_billing.export_portfolio_rest_logic')->post();
  }

}
