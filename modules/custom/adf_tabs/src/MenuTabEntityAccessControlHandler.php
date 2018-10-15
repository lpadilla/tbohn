<?php

namespace Drupal\adf_tabs;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Menu tab entity entity.
 *
 * @see \Drupal\adf_tabs\Entity\MenuTabEntity.
 */
class MenuTabEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\adf_tabs\Entity\MenuTabEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished menu tab entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published menu tab entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit menu tab entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete menu tab entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add menu tab entity entities');
  }

}
