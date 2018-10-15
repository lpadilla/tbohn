<?php

namespace Drupal\adf_tabs\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "menu_tab_resource_rest",
 *   label = @Translation("Recursos de los menu tabs"),
 *   uri_paths = {
 *     "canonical" = "/adf_tabs/menu-tab",
 *     "https://www.drupal.org/link-relations/create" = "/adf_tabs/menu-tab"
 *   }
 * )
 */
class MenuTabRestResource extends ResourceBase {

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
      $container->get('logger.factory')->get('adf_tabs'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param $data
   *   option selected
   *
   * @return mixed
   */
  public function post($data) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('adf_tabs.menu_tab_rest_logic')->post($data);

  }

}
