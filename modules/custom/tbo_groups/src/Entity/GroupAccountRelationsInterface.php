<?php

namespace Drupal\tbo_groups\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Group account relations entities.
 *
 * @ingroup tbo_groups
 */
interface GroupAccountRelationsInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Group account role entity name.
   *
   * @return string
   *   Name of the Group account role entity.
   */
  public function getName();

  /**
   * Sets the Group account role entity name.
   *
   * @param string $name
   *   The Group account role entity name.
   *
   * @return \Drupal\tbo_groups\Entity\GroupAccountRoleEntityInterface
   *   The called Group account role entity entity.
   */
  public function setName($name);

  /**
   * Gets the Group account role entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Group account role entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Group account role entity creation timestamp.
   *
   * @param int $timestamp
   *   The Group account role entity creation timestamp.
   *
   * @return \Drupal\tbo_groups\Entity\GroupAccountRoleEntityInterface
   *   The called Group account role entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Group account role entity published status indicator.
   *
   * Unpublished Group account role entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Group account role entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Group account role entity.
   *
   * @param bool $published
   *   TRUE to set this Group account role entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_groups\Entity\GroupAccountRoleEntityInterface
   *   The called Group account role entity entity.
   */
  public function setPublished($published);

}