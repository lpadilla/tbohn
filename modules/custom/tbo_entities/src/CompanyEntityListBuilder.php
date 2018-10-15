<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;

/**
 * Defines a class to build a listing of Company entity entities.
 *
 * @ingroup tbo_entities
 */
class CompanyEntityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Id empresa');
    $header['document_number'] = $this->t('Número de documento');
    $header['company_name'] = $this->t('Empresa');
    $header['segment'] = $this->t('Segmento');
    $header['status'] = $this->t('Activo');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tbo_entities\Entity\CompanyEntity */
    $row['id'] = $entity->id();
    $row['document_number'] = $entity->getDocumentNumber();
    $row['company_name'] = $entity->getCompanyName();
    $row['segment'] = $entity->getCompanySegment();
    $row['status'] = $entity->isPublished();
    return $row + parent::buildRow($entity);
  }

}
