<?php

namespace Drupal\tbo_groups;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\tbo_groups\Entity\GroupAccountRelationsInterface;

/**
 * Defines the storage handler class for Group account relations entities.
 *
 * This extends the base storage class, adding required special handling for
 * Group account relations entities.
 *
 * @ingroup tbo_groups
 */
class GroupAccountRelationsStorage extends SqlContentEntityStorage implements GroupAccountRelationsStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(GroupAccountRelationsInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {group_account_relations_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {group_account_relations_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(GroupAccountRelationsInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {group_account_relations_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('group_account_relations_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
