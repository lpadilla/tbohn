<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Notification entity entities.
 *
 * @ingroup tbo_entities
 */
interface NotificationEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Notification entity name.
   *
   * @return string
   *   Name of the Notification entity.
   */
  public function getName();

  /**
   * Sets the Notification entity name.
   *
   * @param string $name
   *   The Notification entity name.
   *
   * @return \Drupal\tbo_entities\Entity\NotificationEntityInterface
   *   The called Notification entity entity.
   */
  public function setName($name);

  /**
   * Gets the Notification entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Notification entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Notification entity creation timestamp.
   *
   * @param int $timestamp
   *   The Notification entity creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\NotificationEntityInterface
   *   The called Notification entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Notification entity published status indicator.
   *
   * Unpublished Notification entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Notification entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Notification entity.
   *
   * @param bool $published
   *   TRUE to set this Notification entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\NotificationEntityInterface
   *   The called Notification entity entity.
   */
  public function setPublished($published);

}
