<?php

namespace Drupal\tbo_billing\Repository;

/**
 * Class BillingRepository.
 *
 * @package Drupal\tbo_billing\Repository
 */
class BillingRepository {

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
   *
   */
  public function getAlladminCompany($company_id) {
    $query = $this->database->select('company_user_relations_field_data', 'relation');
    $query->distinct();
    $query->join('users_field_data', 'user', 'relation.users = user.uid');
    $query->join('user__roles', 'role', 'user.uid = role.entity_id');
    $query->condition('role.roles_target_id', 'admin_company');
    $query->condition('relation.company_id', $company_id);
    $query->addField('user', 'name');
    $query->addField('user', 'full_name');
    $query->addField('user', 'mail');
    return $query->execute()->fetchAll();
  }

}
