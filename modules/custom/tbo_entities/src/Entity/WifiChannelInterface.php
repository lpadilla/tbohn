<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Wifi channel entities.
 *
 * @ingroup tbo_entities
 */
interface WifiChannelInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Wifi channel name.
   *
   * @return string
   *   Name of the Wifi channel.
   */
  public function getName();

  /**
   * Sets the Wifi channel name.
   *
   * @param string $name
   *   The Wifi channel name.
   *
   * @return \Drupal\|\Entity\WifiChannelInterface
   *   The called Wifi channel entity.
   */
  public function setName($name);

  /**
   * Gets the Wifi channel keyword.
   *
   * @return string
   *   Keyword of the Wifi channel.
   */
  public function getKeyword();

  /**
   * Sets the Wifi channel keyword.
   *
   * @param string $keyword
   *   The Wifi channel keyword.
   *
   * @return \Drupal\tbo_entities\Entity\WifiChannelInterface
   *   The called Wifi channel entity.
   */
  public function setKeyword($keyword);

  /**
   * Gets the Wifi channel creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Wifi channel.
   */
  public function getCreatedTime();

  /**
   * Sets the Wifi channel creation timestamp.
   *
   * @param int $timestamp
   *   The Wifi channel creation timestamp.
   *
   * @return \Drupal\tbo_entities\Entity\WifiChannelInterface
   *   The called Wifi channel entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Wifi channel published status indicator.
   *
   * Unpublished Wifi channel are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Wifi channel is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Wifi channel.
   *
   * @param bool $published
   *   TRUE to set this Wifi channel to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\tbo_entities\Entity\WifiChannelInterface
   *   The called Wifi channel entity.
   */
  public function setPublished($published);

}
