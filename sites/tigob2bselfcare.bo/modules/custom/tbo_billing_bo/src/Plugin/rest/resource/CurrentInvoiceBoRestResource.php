<?php
namespace Drupal\tbo_billing_bo\Plugin\rest\resource;

use Drupal\tbo_billing\Plugin\rest\resource\CurrentInvoiceRestResource;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "current_invoice_bo_rest_resource",
 *   label = @Translation("Current Invoice Rest Resource BO"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/invoice/bo/current"
 *   }
 * )
 */
class CurrentInvoiceBoRestResource extends CurrentInvoiceRestResource {
  /**
   * A current user instance..
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
   * @return mixed
   */
  public function get() {
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();
    return \Drupal::service('tbo_billing_bo.current_invoice_bo_logic')->get($this->currentUser);
  }
}