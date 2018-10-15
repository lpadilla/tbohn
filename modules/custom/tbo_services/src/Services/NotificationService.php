<?php

namespace Drupal\tbo_services\Services;

use Drupal\tbo_entities\Entity\NotificationDetailEntity;

/**
 * Class NotificationService.
 *
 * @package Drupal\tbo_services\Repository
 */
class NotificationService {

  /**
   * Return all tigo admins.
   */
  public function createRelationNotification($uid, $notification_id, $is_verified = FALSE) {
    $pending = 0;
    if ($is_verified) {
      $pending = 1;
    }
    // Create relation.
    $relation = NotificationDetailEntity::create();
    $relation->set('user_id', $uid);
    $relation->set('notification_id', $notification_id);
    $relation->set('pending', $pending);
    $status = $relation->save();

    // Update accept notification.
    $repository = \Drupal::service('tbo_services.tbo_services_repository');
    $update_accept = $repository->updateAcceptNotification($notification_id);

    return $status;
  }

}
