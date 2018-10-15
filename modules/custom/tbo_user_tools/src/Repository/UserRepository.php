<?php

namespace Drupal\tbo_user_tools\Repository;

/**
 * Class UserRepository.
 *
 * @package Drupal\tbo_user_tools\Repository
 */
class UserRepository {

  /**
   * Storage the conexion service to database.
   *
   * @var \Drupal\Core\Database\Connection
   *   Storage the conexion service to database.
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Implements getUserRelation().
   *
   * @param mixed $user_id
   *   The userId.
   *
   * @return mixed
   *   Return id.
   */
  public function getUserRelation($user_id) {
    $query = $this->database->select('company_user_relations_field_data', 'cur');
    $query->fields('cur', ['id']);
    $query->condition('cur.users', $user_id);
    return $query->execute()->fetchAll();
  }

  /**
   * Implements getUserInvitation().
   *
   * @param mixed $user_id
   *   The userId.
   *
   * @return mixed
   *   Return invitation_id.
   */
  public function getUserInvitation($user_id) {
    $query = $this->database->select('invitation_access_entity_field_data', 'invitation');
    $query->fields('invitation', ['id']);
    $query->condition('invitation.user_id', $user_id);
    return $query->execute()->fetchField();
  }

  /**
   * Implements deleteUserTigoId().
   *
   * @param mixed $user_id
   *   The userId.
   *
   * @return int
   *   Return state.
   */
  public function deleteUserTigoId($user_id) {
    $query = $this->database->delete('openid_connect_authmap');
    $query->condition('uid', $user_id);
    return $query->execute();
  }

}
