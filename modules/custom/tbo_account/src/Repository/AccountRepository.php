<?php

namespace Drupal\tbo_account\Repository;

use Drupal\Core\Database\Query\Condition;

/**
 * Class AccountRepository.
 *
 * @package Drupal\tbo_account\Repository
 */
class AccountRepository {

  /**
   * Storage the conexion service to database.
   */
  protected $database;

  /**
   *
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Implements function hasRole for validate users roles.
   *
   * @param $rid
   *
   * @return bool
   */
  public function hasRole($rid) {
    return in_array($rid, \Drupal::currentUser()->getRoles());
  }

  /**
   * Gets all the companies that have associated an tigo_admin for filters and dynamic column.
   *
   * @param array $filters
   * @param $colums_table
   *
   * @return mixed
   */
  public function getQueryTigoAdminCompanies($filters = [], $colums_table, $limit = '') {
    $function = __FUNCTION__;
    $result = [];

    // Create Query.
    $query = $this->database->select('company_user_relations_field_data', 'relation');
    $query->distinct();
    $query->join('company_entity_field_data', 'company', 'relation.company_id = company.id');
    $query->join('users_field_data', 'user', 'relation.associated_id = user.uid');

    // Add fields to query.
    if (count($colums_table) > 0) {
      foreach ($colums_table as $key => $data) {
        if ($data['type'] == 'company') {
          $query->addField('company', $data['value']);
        }
        else {
          if ($data['type'] == 'user') {
            $query->addField('user', $data['value']);
          }
        }
      }
    }

    if (count($filters) > 0) {
      // Add filters to query.
      foreach ($filters as $key => $filter) {
        if ($key == 'name' || $key == 'full_name') {
          $query->condition("user.$key", '%' . $query->escapeLike($filter) . '%', 'LIKE');
        }
        elseif ($key == 'status_tigo_admin') {
          $query->condition("user.status", '%' . $query->escapeLike($filter) . '%', 'LIKE');
        }
        else {
          $query->condition("company.$key", '%' . $query->escapeLike($filter) . '%', 'LIKE');
        }
      }
    }

    // Validate Roles for query.
    if ($this->hasRole('tigo_admin')) {
      $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
      $query->condition('compUser.users', \Drupal::currentUser()->id());
    }

    $query->orderBy('company.document_number', 'ASC');

    // Add limit to query.
    if (!empty($limit)) {
      $query->range(0, $limit);
    }
    $result = $query->execute()->fetchAll();
    // Save cache.
    return $result;
  }

  /**
   * Get all business by tigo_admin.
   *
   * @param $tigo_admin_id
   */
  public function getAllCompanyTigoAdmin($tigo_admin_id) {
    $query = $this->database->select('company_user_relations_field_data', 'company_user');
    $query->distinct();
    $query->addField('company_user', 'company_id');
    $query->condition('associated_id', $tigo_admin_id);
    $result = $query->execute()->fetchCol();

    return $result;
  }

  /**
   * Get all business users by tigo_admin.
   *
   * @param array $company
   *
   * @return mixed
   */
  public function getAllUserIdCompanyTigoAdmin($company = []) {
    $query = $this->database->select('company_user_relations_field_data', 'company_user');
    $query->distinct();
    $query->addField('company_user', 'users');
    $query->condition('company_id', $company, 'IN');
    $result = $query->execute()->fetchCol();

    return $result;
  }

  /**
   * Get all companies.
   *
   * @param array $filters
   * @param array $colums_table
   * @param array $config_paginate
   * @param array $others_field
   * @param string $tigoadmin
   *
   * @return mixed
   */
  public function getQueryCompanies($filters = [], $colums_table = [], $config_paginate = [], $others_field = [], $tigoadmin = '') {

    $query = $this->database->select('company_entity_field_data', 'company');
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');

    if (empty($tigoadmin)) {
      $query->innerJoin('users_field_data', 'user', 'user.uid = compUser.users');
    }
    else {
      $query->innerJoin('user__roles', 'user_rol', 'user_rol.entity_id = compUser.users');
      $query->leftJoin('users_field_data', 'user', "user.uid = user_rol.entity_id AND user_rol.roles_target_id = 'admin_company'");
    }

    // Add fields to query.
    if (count($colums_table) > 0) {
      foreach ($colums_table as $key => $data) {
        if ($data['type'] == 'company') {
          $query->addField('company', $data['value']);
        }
        else {
          if ($data['type'] == 'user') {
            $query->addField('user', $data['value']);
          }
        }
      }
    }

    if (!empty($others_field)) {
      foreach ($others_field as $key => $data) {
        foreach ($data as $value) {
          $query->addField($key, $value);
        }
      }
    }

    // Add filters to query.
    if (count($filters) > 0) {
      foreach ($filters as $key => $filter) {
        if ($key == 'document_number' || $key == 'name') {
          $query->condition("company.$key", '%' . $query->escapeLike($filter) . '%', 'LIKE');
        }
        else {
          if ($key == 'user_name' || $key == 'full_name') {
            $query->condition("user.full_name", '%' . $query->escapeLike($filter) . '%', 'LIKE');
          }
          else {
            if ($key == 'mail') {
              $query->condition('user.mail', '%' . $query->escapeLike($filter) . '%', 'LIKE');
            }
          }
        }
      }
    }

    if (empty($tigoadmin)) {
      // Validate Roles for query.
      if ($this->hasRole('tigo_admin') && !$this->hasRole('super_admin')) {
        $query->condition('compUser.associated_id', \Drupal::currentUser()->id());
      }
    }
    else {
      $query->condition('compUser.associated_id', $tigoadmin);
    }

    $query->orderBy('company.created', 'DESC');

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

  /**
   * Get all companies for name in autocomplete.
   *
   * @param $company_name
   *
   * @return mixed
   */
  public function getAutocompleteCompanies($company_name) {
    $config = \Drupal::config("tbo_account.pagerformconfig");

    $database = \Drupal::database();
    $query = $database->select('company_entity_field_data', 'company');
    $query->distinct();
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
    $query->innerJoin('users_field_data', 'user', 'user.uid = compUser.users');
    $query->addField('company', 'name');
    $query->condition('company.name', '%' . $query->escapeLike($company_name) . '%', 'LIKE');

    return $query->execute()->fetchAll();
  }

  /**
   * Get all emails of the admin_company for company.
   *
   * @param $company_id
   *
   * @return mixed
   */
  public function getAllEmailUserCompany($company_id) {
    $database = \Drupal::database();
    $query = $database->select('company_user_relations_field_data', 'company');
    $query->distinct();
    $query->innerJoin('users_field_data', 'user', 'company.users = user.uid');
    $query->innerJoin('user__roles', 'roles', 'user.uid = roles.entity_id');
    $query->fields('user', ['mail', 'full_name']);
    $query->condition('user.status', 1);
    $query->condition('roles.roles_target_id', 'admin_company');
    $query->condition('company.company_id', $company_id);

    return $query->execute()->fetchAll();
  }

  /**
   * Get all business by admin_company.
   *
   * @param $admin_admin_id
   *
   * @return mixed
   */
  public function getAllCompanyRoleAdminCompany($admin_admin_id) {
    $query = \Drupal::database()->select('company_user_relations_field_data', 'company_user');
    $query->distinct();
    $query->addField('company_user', 'company_id');
    $query->condition('users', $admin_admin_id);
    $result = $query->execute()->fetchCol();

    return $result;
  }

  /**
   * Get company data by company_id.
   *
   * @param $company_id
   *
   * @return mixed
   */
  public function getCompanyDocument($company_id) {
    $query = \Drupal::database()
      ->select('company_entity_field_data', 'company');
    $query->addField('company', 'document_number');
    $query->addField('company', 'document_type');
    $query->condition('company.id', $company_id);
    $query->range(0, 1);
    $result_query = $query->execute()->fetchAll();
    return $result_query;
  }

  /**
   * Get all emails from users super_admin.
   *
   * @param $company_id
   *
   * @return mixed
   */
  public function getAllEmailSuperAdmin($company_id) {
    $database = \Drupal::database();
    $query = $database->select('users_field_data', 'user');
    $query->innerJoin('user__roles', 'roles', 'user.uid = roles.entity_id');
    $query->fields('user', ['mail', 'full_name']);
    $query->condition('user.status', 1);
    $query->condition('roles.roles_target_id', 'super_admin');

    return $query->execute()->fetchAll();
  }

  /**
   * Get company by documentNumber.
   *
   * @param $documentNumber
   *
   * @return mixed
   */
  public function getCompanyToDocumentNumber($documentNumber) {
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->addField('company', 'id');
    $query->condition('document_number', $documentNumber);

    return $query->execute()->fetchField();
  }

  /**
   * Get company by companyId.
   *
   * @param $id
   *
   * @return mixed
   */
  public function getCompanyToCompanyId($id) {
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->addField('company', 'id');
    $query->condition('id', $id);

    return $query->execute()->fetchAll();
  }

  /**
   * Get user by mail.
   *
   * @param $mail
   *
   * @return mixed
   */
  public function getUserByEmail($mail = NULL) {
    $query = $this->database->select('users_field_data', 'user');
    $query->addField('user', 'uid');
    $query->condition('mail', $mail);

    return $query->execute()->fetchField();
  }

  /**
   * Get user with fields and conditions.
   *
   * @param array $fields
   * @param array $conditions
   *
   * @return mixed
   */
  public function getUser($fields = [], $conditions = [], $fetchAssoc = FALSE, $limit = FALSE) {
    $query = $this->database->select('users_field_data', 'user');

    if (!empty($fields)) {
      foreach ($fields as $field) {
        $query->addField('user', $field);
      }
    }

    if (!empty($conditions)) {
      foreach ($conditions as $key => $condition) {
        $query->condition($key, $condition);
      }
    }

    if ($fetchAssoc) {
      return $query->execute()->fetchAssoc();
    }

    if ($limit) {
      $query->range(0, $limit);
    }

    return $query->execute()->fetchField();
  }

  /**
   * Get user by name.
   *
   * @param $name
   *
   * @return mixed
   */
  public function getUserByName($name = NULL) {
    $query = $this->database->select('users_field_data', 'user');
    $query->addField('user', 'uid');
    $query->condition('name', $name);

    return $query->execute()->fetchField();
  }

  /**
   * Get user_create by nauidme.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function getUserCreatedByUid($uid = NULL) {
    $query = $this->database->select('users_field_data', 'user');
    $query->addField('user', 'created');
    $query->condition('uid', $uid);

    return $query->execute()->fetchField();
  }

  /**
   * Get last user.
   *
   * @return mixed
   */
  public function getLastUid() {
    $query = $this->database->select('users_field_data', 'user');
    $query->addField('user', 'uid');
    $query->orderBy('created', 'DESC');
    $query->range(0, 1);

    return $query->execute()->fetchField();
  }

  /**
   * Get last user by changed.
   *
   * @return mixed
   */
  public function getLastUidByChange() {
    $query = $this->database->select('users_field_data', 'user');
    $query->addField('user', 'uid');
    $query->orderBy('changed', 'DESC');
    $query->range(0, 1);

    return $query->execute()->fetchField();
  }

  /**
   * Get last company.
   *
   * @return mixed
   */
  public function getLastCompany() {
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->addField('company', 'id');
    $query->orderBy('changed', 'DESC');
    $query->range(0, 1);

    return $query->execute()->fetchField();
  }

  /**
   * Get user with fields and conditions.
   *
   * @param array $fields
   * @param array $conditions
   *
   * @return mixed
   */
  public function getCompany($fields = [], $conditions = [], $fetchAssoc = FALSE) {
    $query = $this->database->select('company_entity_field_data', 'company');

    if (!empty($fields)) {
      foreach ($fields as $field) {
        $query->addField('company', $field);
      }
    }

    if (!empty($conditions)) {
      foreach ($conditions as $key => $condition) {
        $query->condition($key, $condition);
      }
    }

    if ($fetchAssoc) {
      return $query->execute()->fetchAssoc();
    }

    return $query->execute()->fetchField();
  }

  /**
   * Get user_uid and user_name by mail.
   *
   * @param $mail
   *
   * @return mixed
   */
  public function getUserUidMailByEmail($mail = NULL) {
    $query = $this->database->select('users_field_data', 'user');
    $query->join('user__roles', 'roles', 'roles.entity_id = user.uid');
    $query->fields('user', ['uid', 'name']);
    $query->condition('user.mail', $mail);
    $query->condition('user.status', 1, '=');
    $query->condition('roles.roles_target_id', 'admin_company');

    return $query->execute()->fetchAll();
  }

  /**
   * Get all tigo_admin users.
   *
   * @param int $limit
   *
   * @return mixed
   */
  public function getTigoAdminUsers($limit = 100, $filters = []) {
    if ($limit == 0) {
      $limit = 100;
    }
    $query = $this->database->select('users_field_data', 'user');
    $query->innerJoin('user__roles', 'roles', 'user.uid = roles.entity_id');
    $query->fields('user', ['uid', 'full_name', 'mail', 'status']);
    $query->condition('roles.roles_target_id', 'tigo_admin');

    if (isset($filters)) {
      foreach ($filters as $key => $value) {
        if ($key == 'status') {
          $query->condition('user.' . $key, $value);
        }
        else {
          $query->condition('user.' . $key, '%' . $query->escapeLike($value) . '%', 'LIKE');
        }
      }
    }

    $query->range(0, $limit);
    $results = $query->execute()->fetchAll();

    return $results;
  }

  /**
   * Function __GetNumEnterprises.
   *
   * @param int $id
   *   ID from user Tigo Admin.
   */
  public function getNumEnterprisesByTigoAdmin($id) {
    $query = $this->database->select('company_user_relations_field_data', 'user');
    $query->distinct();
    $query->addField('user', 'company_id');
    $query->condition('user.associated_id', $id);
    $query->groupBy('user.company_id');
    $results = $query->execute()->fetchAll();

    return count($results);
  }

  /**
   * @param $status_value
   * @param $uid
   * @return \Drupal\Core\Database\StatementInterface|int|null
   */
  public function changeStatusUserTigoAdmin($status_value, $uid) {
    $query = $this->database->update('users_field_data')
      ->fields(['status' => $status_value])
      ->condition('uid', $uid);

    return $query->execute();
  }

  /**
   * @param $tigoadmin
   * @param $companies
   * @return bool|\Drupal\Core\Database\StatementInterface|int|null
   */
  public function changeTigoAdminFromCompany($tigoadmin, $companies) {
    $num_updated = FALSE;

    foreach ($companies as $company => $newtigo) {
      $query = $this->database->update('company_user_relations_field_data');
      $query->fields([
        'associated_id' => $newtigo,
      ]);
      $query->condition('company_id', $company);
      $query->condition('associated_id', $tigoadmin);
      $num_updated = $query->execute();
    }

    return $num_updated;
  }

  /**
   * @param array $filters
   * @param $colums_table
   * @param array $config_paginate
   * @param bool $tigo_admin
   * @param $id
   * @return mixed
   */
  public function getQueryOnlyTableCompanies($filters = [], $colums_table, $config_paginate = [], $id) {
    $database = \Drupal::database();
    $query = $database->select('company_entity_field_data', 'company');
    // Validate role tigo_admin.
    $tigo_admin = $this->hasRole('tigo_admin');

    // Validate Roles for query.
    if ($tigo_admin) {
      $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
      $query->condition('compUser.associated_id', $id);
    }

    // Add fields to query.
    if (count($colums_table) > 0) {
      foreach ($colums_table as $key => $data) {
        $query->addField('company', $key);
      }
    }

    // Add filters to query.
    if (count($filters) > 0) {
      foreach ($filters as $key => $filter) {
        $query->condition("company.$key", '%' . $query->escapeLike($filter) . '%', 'LIKE');
      }
    }

    $query->orderBy('company.document_number', 'ASC');

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

  /**
   * @param $state
   * @param $clientId
   * @return \Drupal\Core\Database\StatementInterface|int|null
   */
  public function changeStatusCompany($state, $clientId) {
    $query = \Drupal::database()->update('company_entity_field_data');
    $query->fields([
      'status' => $state,
    ]);
    $query->condition('document_number', $clientId);
    $response = $query->execute();

    return $query->execute();
  }

  /**
   * Delete cache_entity by company.
   *
   * @param $cid
   *
   * @return int
   */
  public function deleteCacheEntityCompany($cid) {
    $deleteCompany = 'values:company_entity:' . $cid;
    $query = $this->database->delete('cache_entity');
    $query->condition('cid', $deleteCompany);
    return $query->execute();
  }

  /**
   * Delete cache_entity by company.
   *
   * @param $cid
   *
   * @return int
   */
  public function deleteEntityCompany($cid) {
    $query = $this->database->delete('company_entity');
    $query->condition('id', $cid);
    return $query->execute();
  }

  /**
   * @param $cid
   * @param array $roles
   * @return mixed
   */
  public function loadUserWithoutRolByCompany($cid, $roles = []) {
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
    $query->innerJoin('user__roles', 'user_roles', 'user_roles.entity_id = compUser.users');
    $query->distinct();
    $query->fields('compUser', ['users']);
    $query->condition('company.id', $cid);
    $query->condition('user_roles.roles_target_id', $roles, 'NOT IN');
    return $query->execute()->fetchAll();
  }

  /**
   * @param $user
   * @param $cid
   * @return mixed
   */
  public function loadUserInAnotherCompany($user, $cid) {
    // Validate user in another enterprise.
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
    $query->distinct();
    $query->fields('compUser', ['users']);
    $query->condition('compUser.users', $user);
    $query->condition('compUser.company_id', $cid, '!=');
    return $query->execute()->fetchAll();
  }

  /**
   * Delete rows in company_entity_field_data for companyId.
   *
   * @param $cid
   *
   * @return int
   */
  public function deleteRegisterCompanyEntityFieldData($cid) {
    $query = \Drupal::database()->delete('company_entity_field_data');
    $query->condition('id', $cid);
    return $query->execute();
  }

  /**
   * Get company_user_relations_field_data with fields and conditions.
   *
   * @param $cid
   *
   * @return mixed
   */
  public function getUsersRelationsCompany($cid) {
    $query = $this->database->select('company_user_relations_field_data', 'company');
    $query->fields('company', ['id']);
    $query->condition('company.company_id', $cid);
    return $query->execute()->fetchAll();
  }

  /**
   * Get all companies by user.
   *
   * @param array $fields
   * @param array $conditions
   *
   * @return mixed
   */
  public function loadUserRelationCompany($fields = [], $conditions = []) {
    $query = $this->database->select('company_user_relations_field_data', 'userRelation');
    $query->innerJoin('company_entity_field_data', 'company', 'userRelation.company_id = company.id');
    $query->distinct();

    // Add fields.
    if (!empty($fields)) {
      foreach ($fields as $key => $field) {
        foreach ($field as $key_field => $data) {
          $query->addField($key, $data);
        }
      }
    }

    // Add conditions.
    if (!empty($conditions)) {
      foreach ($conditions as $key_condition => $condition) {
        $query->condition($key_condition, $condition);
      }
    }

    return $query->execute()->fetchAll();
  }

  /**
   * Get company_user_relations_field_data with fields and conditions.
   *
   * @param $user_uid
   * @param $company_uid
   *
   * @return mixed
   */
  public function getUsersRelationsCompanyAndUser($user_uid, $company_uid) {
    $query = $this->database->select('company_user_relations_field_data', 'cur');
    $query->fields('cur', ['id']);
    $query->condition('cur.users', $user_uid);
    $query->condition('cur.company_id', $company_uid);
    return $query->execute()->fetchField();
  }

  /**
   * @param $table
   * @param $fields
   * @param $fieldCondition
   * @param $valueCondition
   * @param bool $assocField
   * @return mixed
   */
  public function getData($table, $fields, $fieldCondition, $valueCondition, $assocField = FALSE) {
    $query = $this->database->select($table, 't')
      ->fields('t', $fields)
      ->condition($fieldCondition, $valueCondition, '=');
    $data = ($assocField != TRUE) ? $query->execute()->fetchAssoc() : $query->execute()->fetchField();
    return $data;
  }

  /**
   * @param $company_id
   * @param array $fields
   * @param array $filters
   * @param array $pager
   * @param bool $getRoles
   * @param bool $getCompanies
   * @param bool $group
   */
  public function getUsersByCompanyWithFilterAndConditions($company_id, $fields = [], $filters = [], $pager = [], &$getRoles = FALSE, &$getCompanies = FALSE, &$group = FALSE) {
    $query = $this->database->select('company_user_relations_field_data', 'compUserRole');
    $query->innerJoin('company_entity_field_data', 'company', 'compUserRole.company_id = company.id');
    $query->innerJoin('users_field_data', 'user', 'user.uid = compUserRole.users');
    $query->innerJoin('user__roles', 'roles', 'compUserRole.users = roles.entity_id');
    $query->addField('user', 'uid');
    $query->condition('compUserRole.company_id', $company_id);
    $query->condition('roles.roles_target_id', 'administrator', '<>');
    $query->condition('roles.roles_target_id', 'super_admin', '<>');
    $query->condition('roles.roles_target_id', 'tigo_admin', '<>');

    // Current users.
    $account = \Drupal::currentUser();

    // Add fields to query.
    if (count($fields) > 0) {
      foreach ($fields as $key => $data) {
        if ($data['type'] == 'role') {
          // Los roles se obtienen posteriormente.
          $getRoles = TRUE;
        }
        elseif ($data['type'] == 'group') {
          $group = TRUE;
        }
        else {
          $query->addField($data['type'], $data['service_field']);
        }
      }
    }

    /**
     * filter logs by user's filters
     */
    if (!empty($filters)) {
      foreach ($filters as $condition => $value) {
        if ($condition == 'full_name' || $condition == 'mail' || $condition == 'document_number' || $condition == 'document_type' || $condition == 'phone_number') {
          $query->condition('user.' . $condition, '%' . $query->escapeLike($value) . '%', 'LIKE');
        }
        elseif ($condition == 'company_name') {
          $query->condition('company.' . $condition, '%' . $query->escapeLike($value) . '%', 'LIKE');
        }
        elseif ($condition == 'user_role' && $value) {
          $query->condition('roles.roles_target_id', $value);
        }
      }
    }

    // Add limit to query.
    if (!empty($pager['number_pages']) && !empty($pager['number_rows_pages'])) {
      $query->range(0, $pager['number_pages'] * $pager['number_rows_pages']);
    }

    // Add order CU.
    $query->orderBy('user.created', 'DESC');

    $result = $query->execute()->fetchAll();

    return $result;
  }

  /**
   * @param array $fields
   * @param array $config_paginate
   * @return array
   */
  public function getUserByCompaniesAndTigoAdmin($fields = [], $config_paginate = []) {
    $response = [];
    $roles = \Drupal::currentUser()->getRoles();

    $query = $this->database->select('company_entity_field_data', 'company');
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
    $query->innerJoin('users_field_data', 'user', 'user.uid = compUser.users');
    $query->innerJoin('user__roles', 'rol', "user.uid = rol.entity_id");

    // Add fields to query.
    if (count($fields) > 0) {
      // Add fields.
      $query->addField('user', 'uid');
      $query->addField('company', 'id');
      //Others fields
      foreach ($fields as $key => $data) {
        $query->addField($data['type'], $data['service_field']);
      }
      //Add field for filter tigo_admin
      $query->addField('compUser', 'associated_id');
    }

    // Add conditions.
    $query->condition('user.status', 1, '=');
    $query->condition('rol.roles_target_id', 'admin_company', '=');

    // Add order by.
    $query->orderBy('company.created', 'DESC');

    // Get config paginate.
    $pages = isset($config_paginate['number_rows_pages']) ? $config_paginate['number_rows_pages'] : '';
    $page_elements = isset($config_paginate['number_pages']) ? $config_paginate['number_pages'] : '';

    // Add limit to query.
    if (!empty($pages) && !empty($page_elements)) {
      $query->range(0, $pages * $page_elements);
    }

    $response = $query->execute()->fetchAll();

    return $response;
  }

  /**
   *
   */
  public function getCompanyByAutocomplete($search = '') {
    // Get Roles.
    $roles = \Drupal::currentUser()->getRoles();
    $user_id = \Drupal::currentUser()->id();

    // Query.
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->fields('company', ['id', 'name']);
    $query->condition('company.name', '%' . $search . '%', 'LIKE');

    // Add condition if the user is tigo admin or admin empresa.
    if ((!in_array('administrator', $roles) || !in_array('super_admin', $roles)) && (in_array('tigo_admin', $roles) || in_array('admin_company', $roles))) {
      $query->innerJoin('company_user_relations_field_data', 'company_data', 'company_data.company_id = company.id');

      if (in_array('tigo_admin', $roles) && in_array('admin_company', $roles)) {
        $condition = $query->orConditionGroup()
          ->condition('company_data.associated_id', $user_id, '=')
          ->condition('company_data.users', $user_id, '=');
        $query->condition($condition);
      }
      elseif (in_array('admin_company', $roles) && !in_array('tigo_admin', $roles)) {
        $query->condition('company_data.users', $user_id, '=');
      }
      elseif (in_array('tigo_admin', $roles)) {
        $query->condition('company_data.associated_id', $user_id, '=');
      }
    }

    $response = $query->execute()->fetchAll();

    return $response;
  }

  /**
   * Autocompleteemail.
   *
   * @return string
   *   Return Hello string.
   */
  public function autocompleteEmail($mail) {
    $query = $this->database->select('users_field_data', 'user');
    $query->join('user__roles', 'roles', 'roles.entity_id = user.uid');
    $query->addField('user', 'mail');
    $query->condition('user.mail', '%' . $mail . '%', 'LIKE');
    $query->condition('user.status', 1, '=');
    $query->condition('roles.roles_target_id', 'admin_company');
    $mails = $query->execute()->fetchAll();

    return $mails;
  }

  /**
   * @param $cid
   * @param array $roles
   * @return mixed
   */
  public function loadUserWithRolByCompany($cid, $roles = []) {
    $query = $this->database->select('company_entity_field_data', 'company');
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
    $query->innerJoin('user__roles', 'user_roles', 'user_roles.entity_id = compUser.users');
    $query->innerJoin('users_field_data', 'user', 'user_roles.entity_id = user.uid');
    $query->distinct();
    $query->fields('compUser', ['users']);
    $query->fields('user', ['name', 'mail', 'full_name', 'phone_number']);
    $query->condition('company.id', $cid);
    $query->condition('user_roles.roles_target_id', $roles, 'IN');
    return $query->execute()->fetchAll();
  }

}
