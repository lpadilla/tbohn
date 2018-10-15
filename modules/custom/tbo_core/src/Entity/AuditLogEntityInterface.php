<?php

namespace Drupal\tbo_core\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Audit log entity entities.
 *
 * @ingroup tbo_core
 */
interface AuditLogEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Audit log entity name.
   *
   * @return string
   *   Name of the Audit log entity.
   */
  public function getName();

  /**
   * Sets the Audit log entity name.
   *
   * @param string $name
   *   The Audit log entity name.
   *
   * @return \Drupal\tbo_core\Entity\AuditLogEntityInterface
   *   The called Audit log entity entity.
   */
  public function setName($name);

  /**
   * Gets the Audit log entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Audit log entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Audit log entity creation timestamp.
   *
   * @param int $timestamp
   *   The Audit log entity creation timestamp.
   *
   * @return \Drupal\tbo_core\Entity\AuditLogEntityInterface
   *   The called Audit log entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Audit log entity published status indicator.
   *
   * Unpublished Audit log entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Audit log entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Audit log entity.
   *
   * @param bool $published
   *   TRUE to set this Audit log entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_core\Entity\AuditLogEntityInterface
   *   The called Audit log entity entity.
   */
  public function setPublished($published);

}
