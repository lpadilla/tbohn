<?php

namespace Drupal\tbo_core\Repository;

use Drupal\user\Entity\Role;
use Drupal\adf_core\Base\BaseApiCache;

/**
 * Class CoreRepository.
 *
 * @package Drupal\tbo_core\Repository
 */
class CoreRepository {


  /**
   * Storage the conexion service to database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Get the role name.
   *
   * @param string $role
   *   Role.
   *
   * @return mixed
   *   Role name.
   *
   * @throws \Exception
   */
  public function getRoleName($role) {
    // Getting data from cache before executing a request.
    $rol = [];
    $response = BaseApiCache::get("entity", 'getRolsName', array_merge([], []));
    if ($response !== FALSE && !is_null($response)) {
      $rol = $response;
    }
    else {
      $roles = Role::loadMultiple();
      foreach ($roles as $key => $data) {
        $rol[$key] = $data->get('label');
      }

      // Save categories in cache.
      BaseApiCache::set("entity", 'getRolsName', array_merge([], []), $rol, 4320);
    }

    return $rol[$role];
  }

  /**
   * Get audit logs by filters formatted.
   *
   * @param array $filters
   *   Filters.
   * @param array $table_columns
   *   Table columns.
   * @param array $config_paginate
   *   Pagination configuration.
   * @param bool $id
   *   Log field flag.
   *
   * @return mixed
   *   Returns the audit logs found.
   */
  public function getAuditLogsByFilter($filters = [], $table_columns = [], $config_paginate = [], $get_error = FALSE) {
    $database = \Drupal::database();
    $query = $database->select('audit_log_entity_field_data', 'log');

    // Add fields to query.
    
    if (count($table_columns) > 0) {
      
      if ($get_error) {
        $query->addField('log', 'error_code');
        $query->addField('log', 'error_message');
      }
      
      foreach ($table_columns as $key => $data) {
        $query->addField('log', $data['value']);
      }
    }

    // Filter logs by user role.
    $account = \Drupal::currentUser();

    if ($account->getRoles(TRUE)) {
      $roles = $account->getRoles();
      // If user role diferent from superadmin make conditions.
      if (!in_array('super_admin', $roles)) {
        if (in_array('admin_company', $roles) || in_array('admin_grupo', $roles) || in_array('tigo_admin', $roles)) {
          if (in_array('tigo_admin', $roles)) {
            // Get all users to tigoAdmin.
            $service = \Drupal::service('tbo_account.repository');
            $companies = $service->getAllCompanyTigoAdmin($account->id());
            $users = $service->getAllUserIdCompanyTigoAdmin($companies);
            array_push($users, $account->id());
            $query->condition('log.user_id', $users, 'IN');
            $query->condition('log.user_role', [
              \Drupal::service('tbo_core.repository')->getRoleName('tigo_admin'),
              \Drupal::service('tbo_core.repository')->getRoleName('admin_company'),
              \Drupal::service('tbo_core.repository')->getRoleName('admin_grupo'),
            ], 'IN');
          }
          elseif (in_array('admin_company', $roles)) {
            $query->condition('log.user_id', $account->id(), '=');
            $query->condition('log.user_role', [
              \Drupal::service('tbo_core.repository')->getRoleName('admin_company'),
              \Drupal::service('tbo_core.repository')->getRoleName('admin_grupo'),
            ], 'IN');
          }
          elseif (in_array('admin_grupo', $roles)) {
            $query->condition('log.user_id', $account->id(), '=');
            $query->condition('log.user_role', [\Drupal::service('tbo_core.repository')->getRoleName('admin_grupo')], 'IN');
          }
        }
      }
    }
    // Has no role but is authenticated user.
    elseif ($account->isAuthenticated()) {
      $query->condition('log.user_id', $account->id(), '=');
    }

    // Filter logs by user's filters.
    if (!empty($filters)) {
      foreach ($filters as $condition => $value) {
        if (isset($condition) && isset($value)) {
          if ($condition == 'date_range') {
            // Get Between.
            $dates_between['date_start'] = $value['date_start'];
            $dates_between['date_end'] = $value['date_end'];

            $query->condition('created', $dates_between, 'BETWEEN');
          }
          elseif ($condition == 'user_role') {
            if (!empty($value)) {
              $roles = [];
              foreach ($value as $key => $rol) {
                array_push($roles, \Drupal::service('tbo_core.repository')->getRoleName($rol));
              }
              $query->condition('user_role', $roles, 'IN');
            }
          }
          else {
            $query->condition($condition, '%' . $value . '%', 'LIKE');
          }
        }
      }
    }

    //TODO: temporary jira TBO2-520
    if (!in_array('administrator', $roles)) {
      $query->condition('event_type', 'Registro crear usuario', '!=');
    }

    $query->orderBy('log.created', 'DESC');

    // Get config paginate.
    $pages = isset($config_paginate['number_rows_pages']) ? $config_paginate['number_rows_pages'] : '';
    $page_elements = isset($config_paginate['number_pages']) ? $config_paginate['number_pages'] : '';

    // Add limit to query.
    if (!empty($pages) && !empty($page_elements)) {
      $query->range(0, $pages * $page_elements);
    }

    $result = $query->execute()->fetchAll();

    return $result;
  }

}
