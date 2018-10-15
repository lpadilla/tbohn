<?php

namespace Drupal\adf_import_data;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Log import data entity entity.
 *
 * @see \Drupal\adf_import_data\Entity\LogImportDataEntity.
 */
class LogImportDataEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\adf_import_data\Entity\LogImportDataEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished log import data entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published log import data entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit log import data entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete log import data entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add log import data entity entities');
  }

}
