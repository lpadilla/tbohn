<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\tbo_entities\Entity\CompanyUserRelationsInterface;

/**
 * Defines the storage handler class for Company user relations entities.
 *
 * This extends the base storage class, adding required special handling for
 * Company user relations entities.
 *
 * @ingroup tbo_entities
 */
class CompanyUserRelationsStorage extends SqlContentEntityStorage implements CompanyUserRelationsStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(CompanyUserRelationsInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {company_user_relations_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {company_user_relations_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(CompanyUserRelationsInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {company_user_relations_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('company_user_relations_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
