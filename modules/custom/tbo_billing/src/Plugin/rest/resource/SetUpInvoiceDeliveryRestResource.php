<?php

namespace Drupal\tbo_billing\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "set_up_invoice_delivery_rest_resource",
 *   label = @Translation("Set up invoice delivery rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo_billing/rest/invoice-delivery",
 *      "https://www.drupal.org/link-relations/create" = "/tbo_billing/rest/invoice-delivery"
 *   }
 * )
 */
class SetUpInvoiceDeliveryRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new SetUpInvoiceDeliveryRestResource object.
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

    // You must to implement the logic of your REST Resource here.
    return \Drupal::service('tbo_billing.set_up_invoice_delivery_rest')->get($this->currentUser);
  }

  /**
   * Responds to POST requests.
   * calls create method.
   *
   * @param $params
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post($params) {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    return \Drupal::service('tbo_billing.set_up_invoice_delivery_rest')->post($this->currentUser, $params);
  }

}
