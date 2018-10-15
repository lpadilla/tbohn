<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Wifi security type entity entities.
 *
 * @ingroup tbo_entities
 */
interface WifiSecurityTypeEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Wifi security type entity name.
   *
   * @return string
   *   Name of the Wifi security type entity.
   */
  public function getName();

  /**
   * Sets the Wifi security type entity name.
   *
   * @param string $name
   *   The Wifi security type entity name.
   *
   * @return \Drupal\tbo_entities\Entity\WifiSecurityTypeEntityInterface
   *   The called Wifi security type entity entity.
   */
  public function setName($name);

  /**
   * Gets the Wifi security type entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Wifi security type entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Wifi security type entity creation timestamp.
   *
   * @param int $timestamp
   *   The Wifi security type entity creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\WifiSecurityTypeEntityInterface
   *   The called Wifi security type entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Wifi security type entity published status indicator.
   *
   * Unpublished Wifi security type entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Wifi security type entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Wifi security type entity.
   *
   * @param bool $published
   *   TRUE to set this Wifi security type entity to published,
   *   FALSE to set it to unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\WifiSecurityTypeEntityInterface
   *   The called Wifi security type entity entity.
   */
  public function setPublished($published);

}
