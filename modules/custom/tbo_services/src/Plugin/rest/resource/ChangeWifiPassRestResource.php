<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "change_wifi_pass_rest_resource",
 *   label = @Translation("Change WiFi Password rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/change-wifi-pass",
 *     "https://www.drupal.org/link-relations/create" = "/tbo-services/rest/change-wifi-pass"
 *   }
 * )
 */
class ChangeWifiPassRestResource extends ResourceBase {

  protected $current_user;

  /**
   * ChangeWifiPassRestResource constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param array $serializer_formats
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
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

    return \Drupal::service('tbo_services.change_wifi_pass_rest_logic')->get();
  }

  /**
   * @param $data
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function post($data) {

    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_services.change_wifi_pass_rest_logic')->post($data);
  }

}
