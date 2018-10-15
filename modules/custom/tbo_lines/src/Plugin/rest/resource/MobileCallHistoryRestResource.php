<?php

namespace Drupal\tbo_lines\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "mobile_call_history_rest_resource",
 *   label = @Translation("Mobile call history rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-lines/mobile-call-history",
 *      "https://www.drupal.org/link-relations/create" =
 *   "/tbo-lines/mobile-call-history"
 *   }
 * )
 */
class MobileCallHistoryRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new MobileCallHistoryRestResource object.
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
   */
  public function __construct(
    array $configuration,
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
      $container->get('logger.factory')->get('tbo_lines')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('tbo_lines.mobile_call_history_rest')->get();
  }

  /**
   * Responds to POST requests.
   *
   * @param $data
   *
   * @return mixed
   */
  public function post($data) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('tbo_lines.mobile_call_history_rest')->post($data);
  }

}
