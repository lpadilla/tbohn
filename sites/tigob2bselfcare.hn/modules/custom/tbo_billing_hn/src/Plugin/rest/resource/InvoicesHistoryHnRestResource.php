<?php

namespace Drupal\tbo_billing_hn\Plugin\rest\resource;

use Drupal\tbo_billing\Plugin\rest\resource\InvoicesHistoryRestResource;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "invoices_history_hn_rest_resource",
 *   label = @Translation("Invoices history rest resource HN"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/billing/history/hn"
 *   }
 * )
 */
class InvoicesHistoryHnRestResource extends InvoicesHistoryRestResource {

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
  
  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    return \Drupal::service('tbo_billing_hn.invoice_history_hn_rest')->get($this->currentUser);
  }

}
