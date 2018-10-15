<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "block_sim_rest_resource",
 *   label = @Translation("Block SIM rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/block-sim",
 *     "https://www.drupal.org/link-relations/create" = "/tbo-services/rest/block-sim"
 *   }
 * )
 */
class BlockSimRestResource extends ResourceBase {

  protected $currentUser;

  /**
   * Implement of constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $pluginId
   *   PluginId.
   * @param mixed $pluginDefinition
   *   PluginDefinition.
   * @param array $serializerFormats
   *   SerializerFormats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, array $serializerFormats, LoggerInterface $logger) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $serializerFormats, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('tbo_services')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return \Drupal::service('tbo_services.block_sim_rest_logic')->get();
  }

  /**
   * Implement of post.
   *
   * @param array|object $data
   *   Datos de entrada.
   *
   * @return array|\Drupal\rest\ModifiedResourceResponse|\Drupal\rest\ResourceResponse
   *   Resultado del llamado al metodo.
   */
  public function post($data) {
    return \Drupal::service('tbo_services.block_sim_rest_logic')->post($data);
  }

}
