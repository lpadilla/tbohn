<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Company user relations entities.
 *
 * @ingroup tbo_entities
 */
interface CompanyUserRelationsInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Company user role entity name.
   *
   * @return string
   *   Name of the Company user role entity.
   */
  public function getName();

  /**
   * Sets the Company user role entity name.
   *
   * @param string $name
   *   The Company user role entity name.
   *
   * @return \Drupal\tbo_entities\Entity\CompanyUserRoleEntityInterface
   *   The called Company user role entity entity.
   */
  public function setName($name);

  /**
   * Gets the Company user role entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Company user role entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Company user role entity creation timestamp.
   *
   * @param int $timestamp
   *   The Company user role entity creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\CompanyUserRoleEntityInterface
   *   The called Company user role entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Company user role entity published status indicator.
   *
   * Unpublished Company user role entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Company user role entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Company user role entity.
   *
   * @param bool $published
   *   TRUE to set this Company user role entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\CompanyUserRoleEntityInterface
   *   The called Company user role entity entity.
   */
  public function setPublished($published);

}