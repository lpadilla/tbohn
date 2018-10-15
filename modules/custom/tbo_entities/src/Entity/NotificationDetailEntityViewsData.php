<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Notification detail entity entities.
 */
class NotificationDetailEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
