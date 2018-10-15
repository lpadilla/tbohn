<?php

namespace Drupal\tbo_lines\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create URL for use rest services
 *
 * @RestResource(
 *   id = "sms_balance_rest_resource",
 *   label = @Translation("Sms balance rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tbo-lines/rest/sms-balance",
 *     "https://www.drupal.org/link-relations/create" = "/tbo-lines/rest/sms-balance"
 *   }
 * )
 */

class SmsBalanceRestResource extends ResourceBase {
	
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
	  
	  return \Drupal::service('tbo_lines.sms_balance_rest_logic')->get();
	}

}