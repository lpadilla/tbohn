<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Company entity entity.
 *
 * @see \Drupal\tbo_entities\Entity\CompanyEntity.
 */
class CompanyEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tbo_entities\Entity\CompanyEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished company entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published company entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit company entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete company entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add company entity entities');
  }

}
