<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "change_wifi_dmz_rest_resource",
 *   label = @Translation("Change wifi dmz rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/change-wifi-dmz",
 *     "https://www.drupal.org/link-relations/create" = "/tbo-services/rest/change-wifi-dmz"
 *   }
 * )
 */
class ChangeWifiDMZRestResource extends ResourceBase {

  protected $current_user;

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
   *   logger.
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
    $container->get('logger.factory')->get('tbo_services')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get() {

    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_services.change_wifi_dmz_rest_logic')->get();
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

    \Drupal::service('page_cache_kill_switch')->trigger();
    $response = \Drupal::service('tbo_services.change_wifi_dmz_rest_logic')->post($data);
    return $response;
  }

}
