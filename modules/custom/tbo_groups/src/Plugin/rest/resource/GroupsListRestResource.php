<?php

namespace Drupal\tbo_groups\Plugin\rest\resource;

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
 *   id = "groups_list_rest_resource",
 *   label = @Translation("Groups list rest resource"),
 *   uri_paths = {
 *     "canonical" = "/tboapi/groups/list"
 *   }
 * )
 */
class GroupsListRestResource extends ResourceBase {

  /**
   * A current u instance.
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
      $container->get('logger.factory')->get('tbo_groups'),
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
    // Use current u after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access list group')) {
      throw new AccessDeniedHttpException();
    }

    $request = $_GET;
    if (isset($_GET['q'])) {
      $q = $_GET['q'];
    }

    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    if (isset($_GET['autocomplete'])) {
      if ($_GET['autocomplete']) {
        $data = $this->getAutocompleteGroups($_GET['autocomplete']);
        $data2 = [];
        foreach ($data as $key => $content) {
          array_push($data2, (array) $content);
        }
        return (new ResourceResponse($data2))->addCacheableDependency($build);
      }
    }

    $filters = $request;
    unset($filters['_format']);
    unset($filters['config_columns']);
    $quantity = 2;
    if (isset($q)) {
      $quantity = 3;
      unset($filters['q']);
    }

    // Get columns table.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_groups');
    $columns_table = $tempstore->get('block_groups_list_columns_' . $request['config_columns']);

    if (count($request) == $quantity && $_GET['_format'] == 'json') {
      $data = $this->getQueryGroups($filters, $columns_table, $request['config_columns']);
    }
    else {
      $data = $this->getQueryGroups($filters, $columns_table, $request['config_columns']);
    }

    $data2 = [];
    foreach ($data as $key => $content) {
      array_push($data2, (array) $content);
    }

    return (new ResourceResponse($data2))->addCacheableDependency($build);
  }

  /**
   * @param array $filters
   * @param $columns_table
   * @param $config_columns
   * @return mixed
   */
  public function getQueryGroups($filters = [], $columns_table, $config_columns) {
    $config = \Drupal::config("tbo_groups.pager_form_config");

    $database = \Drupal::database();
    $query = $database->select('group_entity_field_data', 'g_e');
    $query->distinct();
    $query->innerJoin('users_field_data', 'u', 'u.uid = g_e.administrator');

    // Add fields to query.
    $query->addField('g_e', 'name', 'name');
    $query->addField('g_e', 'administrator', 'group_admin');
    $query->addField('g_e', 'id', 'group_id');

    $query->addField('u', 'name', 'user_name');

    // Add filters to query.
    if (count($filters) > 0) {
      foreach ($filters as $key => $filter) {
        if ($key == 'administrator') {
          $query->condition("u.name", '%' . $query->escapeLike($filter) . '%', 'LIKE');
        }
        elseif ($key == 'name') {
          $query->condition('g_e.name', '%' . $query->escapeLike($filter) . '%', 'LIKE');
        }
      }
    }

    $query->orderBy('g_e.created', 'DESC');

    // Get config paginate.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_groups');
    $config_paginate = $tempstore->get('block_groups_list_columns_pager' . $config_columns);
    $pages = $config_paginate['number_rows_pages'];
    $page_elements = $config_paginate['number_pages'];

    // Add limit to query.
    if (!empty($pages) && !empty($page_elements)) {
      $query->range(0, $pages * $page_elements);
    }

    $result = $query->execute()->fetchAll();
    $exists_name = FALSE;

    /**
     * el valor del nombre del admin es concatenado
     */
    foreach ($result as $key => $value) {
      foreach ($value as $key2 => $value2) {
        if ($key2 == 'user_name') {
          $exists_name = TRUE;
          $name_value = $value2;
          unset($result[$key]->group_admin);
          unset($result[$key]->user_name);
        }
      }
      if ($exists_name) {
        $result[$key]->administrator = $name_value;
      }
    }

    foreach ($result as $key => $value) {
      foreach ($value as $key2 => $value2) {
        if ($key2 == 'group_id') {
          $query = $database->select('group_account_relations_field_data', 'g_a_r');
          $query->condition('g_a_r.group_id', $value2, '=');
          $query->addField('g_a_r', 'id');
          $result_2 = $query->execute()->fetchAll();
          $exists_name = TRUE;
          $associated_accounts_value = count($result_2);
          unset($result[$key]->group_id);
        }
      }
      if ($exists_name) {
        $result[$key]->associated_accounts = $associated_accounts_value;
        $result[$key]->lines = 0;
        $result[$key]->operations = 0;
      }
    }

    /**
     * el valor de la cantidad de cuentas asociadas
     */
    return $result;
  }

  /**
   * @param $group
   * @return mixed
   */
  public function getAutocompleteGroups($group) {
    $config = \Drupal::config("tbo_groups.pagerformconfig");

    $database = \Drupal::database();
    $query = $database->select('group_entity_field_data', 'g_e');
    $query->distinct();
    $query->innerJoin('group_account_relations_field_data', 'g_a_r', 'g_a_r.group_id = g_e.id');
    $query->innerJoin('users_field_data', 'u', 'u.uid = g_a_r.administrator');
    $query->addField('g_e', 'name');
    $query->condition('group_entity.name', '%' . $query->escapeLike($group) . '%', 'LIKE');

    return $query->execute()->fetchAll();
  }

  /**
   * @param $rid
   * @return bool
   */
  public function hasRole($rid) {
    return in_array($rid, $this->currentUser->getRoles());
  }

}
