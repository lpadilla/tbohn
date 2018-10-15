<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Invitation access entity entity.
 *
 * @see \Drupal\tbo_entities\Entity\InvitationAccessEntity.
 */
class InvitationAccessEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tbo_entities\Entity\InvitationAccessEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished invitation access entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published invitation access entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit invitation access entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete invitation access entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add invitation access entity entities');
  }

}
