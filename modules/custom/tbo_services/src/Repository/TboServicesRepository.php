<?php

namespace Drupal\tbo_services\Repository;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\adf_core\Util\UtilString;
use Drupal\tbo_entities\Entity\NotificationEntity;
use Drupal\tbo_entities\Entity\NotificationDetailEntity;
use Drupal\views\ResultRow;

/**
 * Class TboServicesRepository.
 *
 * @package Drupal\tbo_services\Repository
 */
class TboServicesRepository {

  /**
   * Storage the conexion service to database.
   */
  protected $database;

  /**
   * TboServicesRepository constructor.
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Return all tigo admins.
   */
  public function getAllTigoAdmins() {
    // Get all admin company.
    $query = $this->database->select('users_field_data', 'ufd');
    $query->fields('ufd', ['name', 'full_name', 'mail', 'phone_number']);
    $query->innerJoin('company_user_relations_field_data', 'cr', 'ufd.uid = cr.users');
    $query->innerJoin('user__roles', 'ur', 'ufd.uid = ur.entity_id');
    $query->condition('cr.company_id', $_SESSION['company']['id'], '=');
    $query->condition('ur.roles_target_id', 'admin_company', '=');

    $users = $query->execute()->fetchAll();
    return $users;
  }

  /**
   * Get users by notificacion.
   *
   * @param array $roles
   *   The user roles.
   * @param bool $last_id
   *   Get the last id.
   *
   * @return array
   *   Return users.
   */
  public function getUsersByFilterToNotification($roles = [], $last_id = FALSE) {
    // Get all admin company.
    $query = $this->database->select('users_field_data', 'user');
    $query->join('user__roles', 'roles', 'user.uid=roles.entity_id');
    $query->fields('user', ['uid']);
    if (!empty($roles)) {
      $query->condition('roles.roles_target_id', $roles, 'IN');
    }

    // Only actives.
    $query->condition('user.status', 1);
    $query->groupBy('user.uid');

    $users = $query->execute()->fetchAll();
    $all_register = count($users);
    $result = [
      'count' => $all_register,
      'last_id' => (int) $users[$all_register-1]->uid,
    ];

    return $result;
  }

  /**
   * Implements getNotifications().
   *
   * @param array $roles
   *   Allow roles.
   * @param int $notification_type
   *   Notification type.
   *
   * @return mixed
   *   Return notifications.
   */
  public function getNotifications($roles = [], $notification_type = 0) {
    $cid = 'tbo_services:notification:' . __FUNCTION__;
    $arguments = $roles;
    array_push($arguments, $notification_type);
    $hash = UtilString::getHash(empty($arguments) ? "none" : $arguments);
    $name_cache = $cid . ":" . $hash;
    $result = NULL;
    if ($cache = \Drupal::cache()->get($name_cache)) {
      $result = $cache->data;
    }
    else {
      $query = $this->database->select('notification_entity', 'notification');
      $query->join('notification_entity__roles', 'roles', 'notification.id=roles.entity_id');
      $query->fields('notification', [
        'id',
        'type_user',
        'id_last_user',
        'notification_type',
      ]);
      $query->condition('status', 1);
      if (!empty($roles)) {
        $query->condition('roles.roles_target_id', $roles, 'IN');
      }

      // Validate if get type.
      if ($notification_type != 0) {
        $query->condition('notification_type', $notification_type);
      }

      $query->groupBy('notification.id, notification.type_user, notification.id_last_user, notification.notification_type');
      $query->orderBy('notification.weight');

      $result = $query->execute()->fetchAll();

      // Save cache.
      $tags = [
        'notification_entity_list',
      ];

      \Drupal::cache()
        ->set($name_cache, $result, CacheBackendInterface::CACHE_PERMANENT, $tags);
    }

    return $result;
  }

  /**
   * Get notification detail.
   *
   * @param int $notification_id
   *   The notification id.
   * @param mixed $user_id
   *   The user id.
   * @param bool $get_pending
   *   Get pending.
   *
   * @return mixed
   *   Get data.
   */
  public function getNotificationDetail($notification_id = 0, $user_id, $get_pending = FALSE) {
    $cid = 'tbo_services:notification_detail:' . __FUNCTION__;
    $arguments['notification_id'] = $notification_id;
    $hash = UtilString::getHash(empty($arguments) ? "none" : $arguments);
    $name_cache = $cid . ":" . $hash;
    $result = NULL;
    if ($cache = \Drupal::cache()->get($name_cache)) {
      $result = $cache->data;
    }
    else {
      $query = $this->database->select('notification_detail_entity', 'notification');
      $query->fields('notification', ['user_id', 'pending']);
      $query->condition('notification_id', $notification_id);
      $result = $query->execute()->fetchAllKeyed();
      // Save cache.
      $tags = [
        'notification_detail_entity_list',
        'notification_entity:' . $notification_id,
      ];

      \Drupal::cache()
        ->set($name_cache, $result, CacheBackendInterface::CACHE_PERMANENT, $tags);
    }

    if (!empty($result)) {
      if (isset($result[$user_id])) {
        if ($get_pending) {
          if (!$result[$user_id] == 1) {
            return [];
          }
          else {
            // Get id.
            $query = $this->database->select('notification_detail_entity', 'notification');
            $query->addField('notification', 'id');
            $query->condition('user_id', $user_id);
            $query->condition('notification_id', $notification_id);
            $query->condition('pending', 1);
            $result = $query->execute()->fetchAll();
            return $result;
          }
        }
        $result[$user_id] = TRUE;
        return $result[$user_id];
      }
      else {
        return [];
      }
    }

    return [];
  }

  /**
   * Update notification quantity.
   *
   * @param int $notification_id
   *   The notification id.
   *
   * @return int
   *   The transaction status.
   */
  public function updateAcceptNotification($notification_id = 0) {
    // Load notification entity.
    $notification_entity = NotificationEntity::load($notification_id);

    // Get accepted_quantity.
    $quantity = ((int) $notification_entity->get('accepted_quantity')->getString()) + 1;
    // Update notification entity.
    $notification_entity->set('accepted_quantity', $quantity);
    $status = $notification_entity->save();

    return $status;
  }

  /**
   * Delete notification delete.
   *
   * @param mixed $notification_id
   *   The notification id in notification detail.
   *
   * @return int
   *   The transaction status.
   */
  public function deleteRelationNotificationDetail($notification_id) {
    $query = $this->database->delete('notification_detail_entity');
    $query->condition('notification_id', $notification_id);
    return $query->execute();
  }

  /**
   * Update status pending notification.
   *
   * @param int $notification_detail_id
   *   The notification detail id.
   *
   * @return int
   *   Return the transaction status.
   */
  public function updatePendingNotification($notification_detail_id = 0) {
    $notification_detail = NotificationDetailEntity::load($notification_detail_id);
    $notification_detail->set('pending', 0);
    $status = $notification_detail->save();

    return $status;
  }

}
