<?php

namespace Drupal\tbo_core;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Audit log entity entity.
 *
 * @see \Drupal\tbo_core\Entity\AuditLogEntity.
 */
class AuditLogEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tbo_core\Entity\AuditLogEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished audit log entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published audit log entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit audit log entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete audit log entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add audit log entity entities');
  }

}
