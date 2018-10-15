<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Notification detail entity entities.
 *
 * @ingroup tbo_entities
 */
interface NotificationDetailEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Notification detail entity name.
   *
   * @return string
   *   Name of the Notification detail entity.
   */
  public function getName();

  /**
   * Sets the Notification detail entity name.
   *
   * @param string $name
   *   The Notification detail entity name.
   *
   * @return \Drupal\tbo_entities\Entity\NotificationDetailEntityInterface
   *   The called Notification detail entity entity.
   */
  public function setName($name);

  /**
   * Gets the Notification detail entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Notification detail entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Notification detail entity creation timestamp.
   *
   * @param int $timestamp
   *   The Notification detail entity creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\NotificationDetailEntityInterface
   *   The called Notification detail entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Notification detail entity published status indicator.
   *
   * Unpublished Notification detail entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Notification detail entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Notification detail entity.
   *
   * @param bool $published
   *   TRUE to set this Notification detail entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\NotificationDetailEntityInterface
   *   The called Notification detail entity entity.
   */
  public function setPublished($published);

}
