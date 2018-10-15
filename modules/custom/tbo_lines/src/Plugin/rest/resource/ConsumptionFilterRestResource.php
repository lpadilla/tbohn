<?php
namespace Drupal\tbo_lines\Plugin\rest\resource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
/**
 * Create URL for use rest services
 *
 * @RestResource(
 *   id = "consumption_filter_rest_resource",
 *   label = @Translation("Filtros de consumos Resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo_lines/consumption_filters",
 *     "https://www.drupal.org/link-relations/create" = "/tbo_lines/consumption_filters",
 *   }
 * )
 */
class ConsumptionFilterRestResource extends ResourceBase {
	
	/**
	 * ChangeWifiPassRestResource constructor.
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 * @param array $serializer_formats
	 * @param LoggerInterface $logger
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
	public function post($data) {
	
		\Drupal::service('page_cache_kill_switch')->trigger();
		
		return \Drupal::service('tbo_lines.consumption_filter_logic')->post($data);
	}
  
  /**
   * {@inheritdoc}
   */
  public function get() {

    \Drupal::service('page_cache_kill_switch')->trigger();
    $response = \Drupal::service('tbo_lines.consumption_filter_logic')->get();

    return new ResourceResponse($response);
  }
}