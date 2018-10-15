<?php

namespace Drupal\tbo_lines\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services
 *
 * @RestResource(
 *   id = "sms_consumption_history_rest_resource",
 *   label = @Translation("Sms consumption history rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo_lines/rest/sms-consumption-history",
 *     "https://www.drupal.org/link-relations/create" = "/tbo_lines/rest/sms-consumption-history"
 *   }
 * )
 */

class SmsConsumptionHistoryRestResource extends ResourceBase {
	
	/**
	 * SmsBalanceRestResource constructor.
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
		return new static (
			$configuration,
			$plugin_id,
			$plugin_definition,
			$container->getParameter('serializer.formats'),
			$container->get('logger.factory')->get('tbo_lines')
		);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function get() {
		\Drupal::service('page_cache_kill_switch')->trigger();
		
		return \Drupal::service('tbo_lines.sms_consumption_history_rest_logic')->get();
	}
	
	public function post($data) {
		\Drupal::service('page_cache_kill_switch')->trigger();
		
		return \Drupal::service('tbo_lines.sms_consumption_history_rest_logic')->post($data);
	}

}