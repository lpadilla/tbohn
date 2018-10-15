<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Tipo de documento entities.
 */
class DocumentTypeEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Machine name');
    $header['label'] = $this->t('Nombre del tipo de documento');
    $header['abb'] = $this->t('Nombre del tipo de documento abreviado');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();
    $row['document_type_name_abb'] = $entity->get('abb_doc_type');

    return $row + parent::buildRow($entity);
  }

}
