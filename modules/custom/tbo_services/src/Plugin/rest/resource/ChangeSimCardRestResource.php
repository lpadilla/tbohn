<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "change_sim_card_rest_resource",
 *   label = @Translation("change sim card rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/change-sim-card"
 *   }
 * )
 */
class ChangeSimCardRestResource extends ResourceBase {

  /**
   * ChangeWifiPassRestResource constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param array $serializer_formats
   * @param \Psr\Log\LoggerInterface $logger
   * @param AccountProxyInterface $current_user
   * @param \Drupal\tbo_mail\SendMessageInterface $send_message
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

    return \Drupal::service('tbo_services.change_sim_card_rest_logic')->get();
  }

}
