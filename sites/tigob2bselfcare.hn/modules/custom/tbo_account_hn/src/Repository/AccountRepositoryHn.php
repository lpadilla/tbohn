<?php

namespace Drupal\tbo_account_hn\Repository;

use Drupal\tigoapi\TigoApiCache;
use Drupal\Core\Database\Query\Condition;
use Drupal\tbo_account\Repository\AccountRepository;


/**
 * Class AccountRepositoryHn
 * @package Drupal\tbo_account_hn\Repository
 */
class AccountRepositoryHn extends AccountRepository{

  protected $database; //Storage the conexion service to database

  function __construct() {
    $this->database = \Drupal::database();
  }
  

  /**
   * Get all companies
   *
   * @param array $filters
   * @param array $colums_table
   * @param array $config_paginate
   * @param array $others_field
   * @param string $tigoadmin
   * @return mixed
   */
  public function getQueryCompaniesHn($filters = [], $colums_table = [], $config_paginate = [], $others_field = [], $tigoadmin = '') {

    $query = $this->database->select('company_entity_field_data', 'company');
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');


    if (empty($tigoadmin)) {
      $query->innerJoin('users_field_data', 'user', 'user.uid = compUser.users');
    }
    else {
      $query->innerJoin('user__roles', 'user_rol', 'user_rol.entity_id = compUser.users');
      $query->leftJoin('users_field_data', 'user', "user.uid = user_rol.entity_id AND user_rol.roles_target_id = 'admin_company'");
    }
    
    //Add fields to query
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

    //Add filters to query
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
      //Validate Roles for query
      if ($this->hasRole('tigo_admin') && !$this->hasRole('super_admin')) {
        $query->condition('compUser.associated_id', \Drupal::currentUser()->id());
      }
    }
    else {
      $query->condition('compUser.associated_id', $tigoadmin);
    }

    $query->orderBy('company.created', 'DESC');

    //Get config paginate
    $pages = isset($config_paginate['number_rows_pages']) ? $config_paginate['number_rows_pages'] : '';
    $page_elements = isset($config_paginate['number_pages']) ? $config_paginate['number_pages'] : '';

    //Add limit to query
    if (!empty($pages) && !empty($page_elements)) {
      $query->range(0, $pages * $page_elements);
    }

    $result = $query->execute()->fetchAll();

    return $result;
  }

  /**
   * Get all companies for name in autocomplete
   *
   * @param $company_name
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

      foreach ($fields as $key => $data) {
        $query->addField($data['type'], $data['service_field']);
      }
    }

    // Add conditions.
    $query->condition('user.status', 1, '=');
    $query->condition('rol.roles_target_id', 'admin_company', '=');

    // Validate Roles for query.
    if ((!in_array('administrator', $roles) || !in_array('super_admin', $roles)) && (in_array('tigo_admin', $roles))) {
      $query->condition('compUser.associated_id', \Drupal::currentUser()->id());
    }

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

  



}