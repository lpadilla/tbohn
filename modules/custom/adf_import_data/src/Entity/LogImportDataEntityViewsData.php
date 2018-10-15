<?php

namespace Drupal\adf_import_data\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Log import data entity entities.
 */
class LogImportDataEntityViewsData extends EntityViewsData {

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
