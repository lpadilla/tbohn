<?php

namespace Drupal\tbo_account\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "tigo_admin_list_company_rest_resource",
 *   label = @Translation("Tigo admin list company rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/account/tigo-admin-list-company"
 *   }
 * )
 */
class TigoAdminListCompanyRestResource extends ResourceBase {

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
      $container->get('logger.factory')->get('tbo_account'),
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

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $request = $_GET;

    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    // Get columns table.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
    $columns_table = $tempstore->get('tigo_admin_list_company_block' . $request['display']);
    $limit = $tempstore->get('tigo_admin_list_company_block_limit' . $request['display']);

    // Remove data for filters.
    unset($request['_format']);
    unset($request['filter']);
    unset($request['display']);

    // Remove var $q.
    if (isset($_GET['q'])) {
      unset($request['q']);
    }

    $account_repository = \Drupal::service('tbo_account.repository');

    // If exists filters.
    if ($_GET['filter']) {
      // Get data companies and format to return.
      $data = $account_repository->getQueryTigoAdminCompanies($request, $columns_table, $limit);
      $data2 = [];
      foreach ($data as $key => $content) {
        array_push($data2, (array) $content);
      }

      // Return data.
      return (new ResourceResponse($data2))->addCacheableDependency($build);
    }

    // If filters no found then get all data.
    $data = $account_repository->getQueryTigoAdminCompanies($request, $columns_table, $limit);
    $data2 = [];
    foreach ($data as $key => $content) {
      array_push($data2, (array) $content);
    }

    return (new ResourceResponse($data2))->addCacheableDependency($build);
  }

}
