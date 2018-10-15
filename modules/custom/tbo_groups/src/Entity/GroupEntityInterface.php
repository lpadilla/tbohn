<?php

namespace Drupal\tbo_groups\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Group entity entities.
 *
 * @ingroup tbo_groups
 */
interface GroupEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Group entity name.
   *
   * @return string
   *   Name of the Group entity.
   */
  public function getName();

  /**
   * Sets the Group entity name.
   *
   * @param string $name
   *   The Group entity name.
   *
   * @return \Drupal\tbo_groups\Entity\GroupEntityInterface
   *   The called Group entity entity.
   */
  public function setName($name);

  /**
   * Gets the Group entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Group entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Group entity creation timestamp.
   *
   * @param int $timestamp
   *   The Group entity creation timestamp.
   *
   * @return \Drupal\tbo_groups\Entity\GroupEntityInterface
   *   The called Group entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Group entity published status indicator.
   *
   * Unpublished Group entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Group entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Group entity.
   *
   * @param bool $published
   *   TRUE to set this Group entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_groups\Entity\GroupEntityInterface
   *   The called Group entity entity.
   */
  public function setPublished($published);
  
  public function getAssociatedAccounts();
  
  public function setAssociatedAccounts($associated_accounts);
  
  public function getAdministrator();
  
  public function setAdministrator($administrator);
  
}
