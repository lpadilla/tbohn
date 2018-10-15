<?php

namespace Drupal\tbo_services\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services
 *
 * @RestResource(
 *   id = "detalle_ver_producto_fijo_rest_resource",
 *   label = @Translation("Detalle ver producto fijo rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-services/rest/detalle-ver-producto-fijo",
 *     "https://www.drupal.org/link-relations/create" = "/tbo-services/rest/detalle-ver-producto-fijo"
 *   }
 * )
 */

class DetalleVerProductoFijoResource extends ResourceBase {

	protected $currentUser;

	/**
	 * DetalleVerProductoFijoRestResource constructor.
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 * @param array $serializer_formats
	 * @param LoggerInterface $logger
	 * @param AccountProxyInterface $current_user
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
			$container->get('logger.factory')->get('tbo_services'),
			$container->get('current_user')
		);
	}
  
  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   * @return mixed
   */
	public function get() {
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    return \Drupal::service('tbo_services.detalle_ver_producto_fijo_logic')->get($this->currentUser);
	}
}
