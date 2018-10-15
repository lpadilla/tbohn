<?php

namespace Drupal\tbo_core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;

/**
 * Defines a class to build a listing of Audit log entity entities.
 *
 * @ingroup tbo_core
 */
class AuditLogEntityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Log de auditoría ID');
    $header['user_names'] = $this->t('Nombres');
    $header['company_name'] = $this->t('Empresa');
    $header['company_document_number'] = $this->t('Número de documento');
    $header['company_segment'] = $this->t('Segmento');
    $header['user_role'] = $this->t('Rol(es)');
    $header['descripcion'] = $this->t('Descripción');
    $header['old_values'] = $this->t('Anterior');
    $header['new_values'] = $this->t('nuevo');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tbo_core\Entity\AuditLogEntity */
    $row['id'] = $entity->id();
    /*
    $row['name'] = $this->l(
    $entity->label(),
    new Url(
    'entity.audit_log_entity.edit_form', array(
    'audit_log_entity' => $entity->id(),
    )
    )
    );
     */
    $row['user_names'] = $entity->getUserNames();
    $row['company_name'] = $entity->getCompanyName();
    $row['company_document_number'] = $entity->getCompanyDocumentNumber();
    $row['company_segment'] = $entity->getCompanySegment();
    $row['user_role'] = $entity->getUserRole();
    $row['descripcion'] = $entity->getDescription();
    $row['old_values'] = $entity->getOldValues();
    $row['new_values'] = $entity->getNewValues();

    return $row + parent::buildRow($entity);
  }

}
