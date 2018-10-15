<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "change_wifi_channel_rest_resource",
 *   label = @Translation("Change WiFi Channel rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/change-wifi-channel",
 *     "https://www.drupal.org/link-relations/create" =
 *   "/tbo-services/rest/change-wifi-channel"
 *   }
 * )
 */
class ChangeWifiChannelRestResource extends ResourceBase {

  /**
   * ChangeWifiChannelRestResource constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger) {
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

    return \Drupal::service('tbo_services.change_wifi_channel_rest_logic')
      ->get();
  }

  /**
   * Post.
   *
   * @param array $data
   *   Parameter for the service call.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Resourse response.
   */
  public function post(array $data) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_services.change_wifi_channel_rest_logic')
      ->post($data);
  }

}
