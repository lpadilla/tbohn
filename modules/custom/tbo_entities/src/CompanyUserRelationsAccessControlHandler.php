<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Company user relations entity.
 *
 * @see \Drupal\tbo_entities\Entity\CompanyUserRelations.
 */
class CompanyUserRelationsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tbo_entities\Entity\CompanyUserRelationsInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished company user relations entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published company user relations entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit company user relations entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete company user relations entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add company user relations entities');
  }

}
