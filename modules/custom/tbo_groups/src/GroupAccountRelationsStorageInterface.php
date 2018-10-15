<?php

namespace Drupal\tbo_groups;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface GroupAccountRelationsStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Group account relations revision IDs for a specific Group account relations.
   *
   * @param \Drupal\tbo_groups\Entity\GroupAccountRelationsInterface $entity
   *   The Group account relations entity.
   *
   * @return int[]
   *   Group account relations revision IDs (in ascending order).
   */
  public function revisionIds(GroupAccountRelationsInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Group account relations author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Group account relations revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\tbo_groups\Entity\GroupAccountRelationsInterface $entity
   *   The Group account relations entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(GroupAccountRelationsInterface $entity);

  /**
   * Unsets the language for all Group account relations with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
