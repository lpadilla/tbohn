<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Categoria de servicios entities.
 */
class CategoryServicesEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label']         = $this->t('Nombre');
    $header['type_category'] = $this->t('Tipo');
    $header['parameter']     = $this->t('Parámetro');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label']         = $entity->label();
    $row['type_category'] = $entity->get('type_category');
    $row['parameter']     = $entity->get('parameter');

    return $row + parent::buildRow($entity);
  }

}
