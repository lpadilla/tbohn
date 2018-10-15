<?php

namespace Drupal\tbo_emulate_hn\Plugin\rest\resource;

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

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "emulate_session_rest_resource_hn",
 *   label = @Translation("Emulate Session Rest Resource HN"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/emulate/hn/session"
 *   }
 * )
 */
class EmulateSessionHnRestResource extends ResourceBase {

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
      $container->get('logger.factory')->get('tbo_emulate_hn'),
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

    //$this->currentUser = $currentUser;
    
    // Get config name.
    $request = $_GET;
    $config_name = $request['config_name'];

    // Get columns table.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_user');
    $columns_table = $tempstore->get($config_name . $request['config_columns']);
    $config_paginate = $tempstore->get($config_name . '_pager' . $request['config_columns']);
    
    // Get repository.
    $account_repository = \Drupal::service('tbo_account_hn.repository');
    $result_query = $account_repository->getUserByCompaniesAndTigoAdmin($columns_table, $config_paginate);

    $repeat = $data2 = [];
    foreach ($result_query as $key => $data) {
      if (!array_key_exists($data->id, $repeat)) {
        array_push($data2, (array) $data);
        end($data2);
        $last_element = key($data2);
        if (isset($data2[$last_element]['name'])) {
          $data2[$last_element]['name'] = ucwords(strtolower($data2[$last_element]['name']));
        }
        $data2[$last_element]['admin_company'][] = (array) $data;
        $repeat[$data->id] = $last_element;
      }
      else {
        array_push($data2[$repeat[$data->id]]['admin_company'], (array) $data);
      }
    }
    return new ResourceResponse($data2);
    
  }

}
