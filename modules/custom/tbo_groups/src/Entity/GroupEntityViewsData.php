<?php

namespace Drupal\tbo_groups\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Group entity entities.
 */
class GroupEntityViewsData extends EntityViewsData {

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
