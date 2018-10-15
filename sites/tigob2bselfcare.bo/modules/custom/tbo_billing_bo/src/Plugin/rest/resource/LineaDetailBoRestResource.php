<?php
namespace Drupal\tbo_billing_bo\Plugin\rest\resource;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\user\Entity\User;
use Drupal\masquerade\Masquerade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Utility\Token;
use Drupal\user\UserInterface;
use Drupal\tbo_api_bo\TboApiBoClient;
use Drupal\tbo_general\Services\TboConfigServiceInterface;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "linea_detail_bo_rest_resource",
 *   label = @Translation("Detalles de Linea Bo Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/billing/linea_detail_bo"
 *   }
 * )
 */
class LineaDetailBoRestResource extends ResourceBase {
  /**
   * A current user instance..
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
	protected $tboApiBoClient;
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
    AccountProxyInterface $current_user,TboApiBoClient $tbo_api_bo_client) {
    	
    	parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    	$this->currentUser = $current_user; 
    	$this->tboApiBoClient = $tbo_api_bo_client;
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
      $container->get('logger.factory')->get('tbo_billing_bo'),
      $container->get('current_user'),
      $container->get('tbo_api_bo.client')
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
    \Drupal::service('page_cache_kill_switch')->trigger();
    $invoices = [];

    $msisdn = $_GET['msisdn'];

    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params['query'] = [
      //'msisdn' => $msisdn,
    ];
    
    //SE PASA EL PARÁMETRO QUE SE NECESITA
    $params['tokens'] = [
      'msisdn' => $msisdn, 
    ];

    $response=$this->tboApiBoClient->getCustomerBasicPlanInfoByMsisdn($params);

        
    $result['plan'] = $response->planBasicInfo->subPlan;
    $result['addons'] = $response->addons->addons;
    $result['telegroup'] = $response->telegroup->telegroup;

    return new ResourceResponse($result);
  }
}