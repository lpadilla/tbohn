<?php

namespace Drupal\tbo_core\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Audit log entity entities.
 */
class AuditLogEntityViewsData extends EntityViewsData {

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
