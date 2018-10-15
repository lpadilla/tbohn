<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Company entity entities.
 *
 * @ingroup tbo_entities
 */
interface CompanyEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Company entity name.
   *
   * @return string
   *   Name of the Company entity.
   */
  public function getName();

  /**
   * Sets the Company entity name.
   *
   * @param string $name
   *   The Company entity name.
   *
   * @return \Drupal\tbo_entities\Entity\CompanyEntityInterface
   *   The called Company entity entity.
   */
  public function setName($name);

  /**
   * Gets the Company entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Company entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Company entity creation timestamp.
   *
   * @param int $timestamp
   *   The Company entity creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\CompanyEntityInterface
   *   The called Company entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Company entity published status indicator.
   *
   * Unpublished Company entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Company entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Company entity.
   *
   * @param bool $published
   *   TRUE to set this Company entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\CompanyEntityInterface
   *   The called Company entity entity.
   */
  public function setPublished($published);

}
