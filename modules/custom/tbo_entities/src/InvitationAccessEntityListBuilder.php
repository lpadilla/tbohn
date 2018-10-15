<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;

/**
 * Defines a class to build a listing of Invitation access entity entities.
 *
 * @ingroup tbo_entities
 */
class InvitationAccessEntityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Invitation access entity ID');
    $header['user_name'] = $this->t('Nombre del invitado');
    $header['mail'] = $this->t('Mail');
    $header['token'] = $this->t('Token');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tbo_entities\Entity\InvitationAccessEntity */
    $row['id'] = $entity->id();
    $row['user_name'] = $entity->getUserName();
    $row['mail'] = $entity->getMail();
    $row['token'] = $entity->getToken();
    return $row + parent::buildRow($entity);
  }

}
