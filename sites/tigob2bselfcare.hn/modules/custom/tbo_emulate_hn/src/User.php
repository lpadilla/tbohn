<?php

namespace Drupal\tbo_emulate_hn;

use Drupal\Core\Session\AccountProxy;


/**
 * Class Admin.
 *
 * @package Drupal\tbo_emulate_hn
 */
class User {

  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(AccountProxy $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Function to get the users array than can be emulated
   * @return array
   */
  public function getAccessibleUsers() {
    $result_query = [];
    $uid = $this->currentUser->id();
    $roles = $this->currentUser->getRoles();
  
      if(in_array('administrator', $roles) || in_array('super_admin', $roles)) {
        $database = \Drupal::database();
        $query = $database->select('company_entity_field_data', 'company');
        $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
        $query->innerJoin('users_field_data', 'user', 'user.uid = compUser.users');
        $query->innerJoin('user__roles', 'rol', "user.uid = rol.entity_id");
        $query->addField('company', 'name');
        $query->addField('user', 'full_name', 'user_name');
        $query->addField('user', 'uid');
        $query->condition('user.status',1, '=');
        $query->condition('rol.roles_target_id','admin_company', '=');
        $query->orderBy('company.created', 'DESC');
        $result_query = $query->execute()->fetchAll();
      }
      else {
        $database = \Drupal::database();
        $query = $database->select('company_entity_field_data', 'company');
        $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
        $query->innerJoin('users_field_data', 'user', 'user.uid = compUser.users');
        $query->innerJoin('user__roles', 'rol', "user.uid = rol.entity_id");
        $query->addField('company', 'name');
        $query->addField('user', 'full_name', 'user_name');
        $query->addField('user', 'uid');
        $query->condition('user.status',1, '=');
        $query->condition('compUser.associated_id', $uid, '=');
        $query->condition('rol.roles_target_id','admin_company', '=');
        $query->orderBy('company.created', 'DESC');
        $result_query = $query->execute()->fetchAll();
        
      }
    return $result_query;
  }

}
