<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "change_security_type_rest_resource",
 *   label = @Translation("Change Security Type rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/change-security-type",
 *     "https://www.drupal.org/link-relations/create" =
 *   "/tbo-services/rest/change-security-type"
 *   }
 * )
 */
class ChangeSecurityTypeRestResource extends ResourceBase {

  protected $currentUser;

  /**
   * ChangeSecurityTypeRestResource constructor.
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
   *   Logger object.
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

    return \Drupal::service('tbo_services.change_security_type_rest_logic')
      ->get();
  }

  /**
   * Post method.
   *
   * @param array $data
   *   Come.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Resource response.
   */
  public function post(array $data) {

    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_services.change_security_type_rest_logic')
      ->post($data);
  }

}
