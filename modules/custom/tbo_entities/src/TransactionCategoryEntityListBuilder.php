<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Transaction category entity entities.
 */
class TransactionCategoryEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label']    = $this->t('Nombre');
    $header['category'] = $this->t('Categoría');
    $header['card']     = $this->t('card');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label']    = $entity->label();
    $row['category'] = $entity->get('category');
    $row['card']     = $entity->get('card');
    return $row + parent::buildRow($entity);
  }

}
