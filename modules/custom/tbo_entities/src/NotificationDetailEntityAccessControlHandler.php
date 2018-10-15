<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Notification detail entity entity.
 *
 * @see \Drupal\tbo_entities\Entity\NotificationDetailEntity.
 */
class NotificationDetailEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tbo_entities\Entity\NotificationDetailEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished notification detail entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published notification detail entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit notification detail entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete notification detail entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add notification detail entity entities');
  }

}
