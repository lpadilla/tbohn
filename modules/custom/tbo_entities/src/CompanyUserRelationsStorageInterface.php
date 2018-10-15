<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface CompanyUserRelationsStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Company user relations revision IDs for a specific Company user relations.
   *
   * @param \Drupal\tbo_entities\Entity\CompanyUserRelationsInterface $entity
   *   The Company user relations entity.
   *
   * @return int[]
   *   Company user relations revision IDs (in ascending order).
   */
  public function revisionIds(CompanyUserRelationsInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Company user relations author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Company user relations revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\tbo_entities\Entity\CompanyUserRelationsInterface $entity
   *   The Company user relations entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CompanyUserRelationsInterface $entity);

  /**
   * Unsets the language for all Company user relations with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
