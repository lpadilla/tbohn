<?php

namespace Drupal\adf_import_data;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Log import data entity entities.
 *
 * @ingroup adf_import_data
 */
class LogImportDataEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Log import data entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\adf_import_data\Entity\LogImportDataEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.log_import_data_entity.edit_form',
      ['log_import_data_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
