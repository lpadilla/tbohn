<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "notification_rest_resource",
 *   label = @Translation("Notification Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo_services/rest/notification",
 *      "https://www.drupal.org/link-relations/create" = "/tbo_services/rest/notification"
 *   }
 * )
 */
class NotificationRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ServicePortfolioRestResource object.
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

    $this->currentUser = $current_user;
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
      $container->get('logger.factory')->get('tbo_billing'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param array $params
   *   The params to post.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Return state.
   */
  public function post(array $params) {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_services.notification_rest_logic')->post($this->currentUser, $params);
  }

}
