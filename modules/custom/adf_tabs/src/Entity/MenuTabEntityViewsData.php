<?php

namespace Drupal\adf_tabs\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Menu tab entity entities.
 */
class MenuTabEntityViewsData extends EntityViewsData {

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
