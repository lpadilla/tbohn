<?php

namespace Drupal\tbo_permissions\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Create URL for use rest services.
 *
 * @RestResource(
 *   id = "consult_companies_blocked_card_rest_resource",
 *   label = @Translation("Consult Companies Blocked Card rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-permissions/rest/consult-companies-blocked-card",
 *     "https://www.drupal.org/link-relations/create" =
 *   "/tbo-permissions/rest/consult-companies-blocked-card"
 *   }
 * )
 */
class ConsultCompaniesBlockedCardRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
      $container->get('logger.factory')->get('tbo_permissions'),
      $container->get('current_user')
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
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_permissions.consult_companies_blocked_card_rest')
      ->get($this->currentUser);
  }

  /**
   * Responds to POST requests.
   *
   * @param array $data
   *   Data params.
   *
   * @return mixed
   *   Response.
   */
  public function post(array $data) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_permissions.consult_companies_blocked_card_rest')
      ->post($this->currentUser, $data);
  }

}
