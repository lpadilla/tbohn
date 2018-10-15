<?php

namespace Drupal\adf_import_data\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Log import data entity entities.
 *
 * @ingroup adf_import_data
 */
interface LogImportDataEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Log import data entity name.
   *
   * @return string
   *   Name of the Log import data entity.
   */
  public function getName();

  /**
   * Sets the Log import data entity name.
   *
   * @param string $name
   *   The Log import data entity name.
   *
   * @return \Drupal\adf_import_data\Entity\LogImportDataEntityInterface
   *   The called Log import data entity entity.
   */
  public function setName($name);

  /**
   * Gets the Log import data entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Log import data entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Log import data entity creation timestamp.
   *
   * @param int $timestamp
   *   The Log import data entity creation timestamp.
   *
   * @return \Drupal\adf_import_data\Entity\LogImportDataEntityInterface
   *   The called Log import data entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Log import data entity published status indicator.
   *
   * Unpublished Log import data entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Log import data entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Log import data entity.
   *
   * @param bool $published
   *   TRUE to set this Log import data entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\adf_import_data\Entity\LogImportDataEntityInterface
   *   The called Log import data entity entity.
   */
  public function setPublished($published);

}
