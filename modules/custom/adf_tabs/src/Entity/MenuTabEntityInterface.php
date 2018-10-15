<?php

namespace Drupal\adf_tabs\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Menu tab entity entities.
 *
 * @ingroup adf_tabs
 */
interface MenuTabEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Menu tab entity name.
   *
   * @return string
   *   Name of the Menu tab entity.
   */
  public function getName();

  /**
   * Sets the Menu tab entity name.
   *
   * @param string $name
   *   The Menu tab entity name.
   *
   * @return \Drupal\adf_tabs\Entity\MenuTabEntityInterface
   *   The called Menu tab entity entity.
   */
  public function setName($name);

  /**
   * Gets the Menu tab entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Menu tab entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Menu tab entity creation timestamp.
   *
   * @param int $timestamp
   *   The Menu tab entity creation timestamp.
   *
   * @return \Drupal\adf_tabs\Entity\MenuTabEntityInterface
   *   The called Menu tab entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Menu tab entity published status indicator.
   *
   * Unpublished Menu tab entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Menu tab entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Menu tab entity.
   *
   * @param bool $published
   *   TRUE to set this Menu tab entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\adf_tabs\Entity\MenuTabEntityInterface
   *   The called Menu tab entity entity.
   */
  public function setPublished($published);

}
