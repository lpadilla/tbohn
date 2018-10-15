<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Wifi channel entity.
 *
 * @see \Drupal\tbo_entities\Entity\WifiChannel.
 */
class WifiChannelAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tbo_entities\Entity\WifiChannelInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished wifi channel entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published wifi channel entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit wifi channel entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete wifi channel entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add wifi channel entities');
  }

}
