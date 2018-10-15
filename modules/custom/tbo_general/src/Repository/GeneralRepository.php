<?php

namespace Drupal\tbo_general\Repository;

use Drupal\user\Entity\User;

/**
 * Class GeneralRepository.
 *
 * @package Drupal\tbo_general\Repository
 */
class GeneralRepository {

  /**
   * Storage the conexion service to database.
   *
   * @var database
   */
  protected $database;

  /**
   * GeneralRepository constructor.
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Get admin relations and companies.
   */
  public function getAlladminCompanyRelations() {
    $uid = \Drupal::currentUser()->id();
    $query = \Drupal::database()->select('company_user_relations_field_data', 'userCompany');
    $query->join('company_entity_field_data', 'company', 'userCompany.company_id = company.id');
    $query->join('users_field_data', 'user', 'userCompany.users = user.uid');
    $query->addField('company', 'name');
    $query->addField('userCompany', 'company_id');
    $query->addField('user', 'mail');
    $query->condition('userCompany.users', $uid);
    if (isset($_SESSION['masquerading'])) {
      $account = User::load($_SESSION['old_user']);
      $roles = $account->getRoles();
      if (in_array('tigo_admin', $roles)) {
        $query->condition('userCompany.associated_id', $_SESSION['old_user']);
      }
    }

    return $query->execute()->fetchAll();
  }

}
