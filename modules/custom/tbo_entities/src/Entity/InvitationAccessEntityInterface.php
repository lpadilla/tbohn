<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Invitation access entity entities.
 *
 * @ingroup tbo_entities
 */
interface InvitationAccessEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Invitation access entity name.
   *
   * @return string
   *   Name of the Invitation access entity.
   */
  public function getName();

  /**
   * Sets the Invitation access entity name.
   *
   * @param string $name
   *   The Invitation access entity name.
   *
   * @return \Drupal\tbo_entities\Entity\InvitationAccessEntityInterface
   *   The called Invitation access entity entity.
   */
  public function setName($name);

  /**
   * Gets the Invitation access entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Invitation access entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Invitation access entity creation timestamp.
   *
   * @param int $timestamp
   *   The Invitation access entity creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\InvitationAccessEntityInterface
   *   The called Invitation access entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Invitation access entity published status indicator.
   *
   * Unpublished Invitation access entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Invitation access entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Invitation access entity.
   *
   * @param bool $published
   *   TRUE to set this Invitation access entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\InvitationAccessEntityInterface
   *   The called Invitation access entity entity.
   */
  public function setPublished($published);

}
