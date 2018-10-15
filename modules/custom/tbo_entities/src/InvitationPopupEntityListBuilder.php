<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Invitación en Popup entities.
 */
class InvitationPopupEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // $header['id']            = $this->t('Machine name');.
    $header['label'] = $this->t('Nombre');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // $row['id']            = $entity->id();
    $row['label'] = $entity->label();

    return $row + parent::buildRow($entity);
  }

}
