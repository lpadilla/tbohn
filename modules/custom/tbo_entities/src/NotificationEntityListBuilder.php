<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Notification entity entities.
 *
 * @ingroup tbo_entities
 */
class NotificationEntityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t("Nombre");
    $header['status'] = $this->t("Estado");
    $header['type'] = $this->t("Tipo");
    $header['send_quantity'] = $this->t("Enviadas");
    $header['accepted_quantity'] = $this->t("Realizadas");
    $header['weight'] = $this->t("Peso");
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tbo_entities\Entity\NotificationEntity */
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.notification_entity.edit_form',
      ['notification_entity' => $entity->id()]
    );
    // 0 => 'Verificar cuenta', 1 => 'Actualización de datos', 2 => 'Otro'
    $type = $entity->get('notification_type')->getString();
    if ($type == 0) {
      $type = 'Verificar cuenta';
    }
    elseif ($type == 1) {
      $type = 'Actualización de datos';
    }
    else {
      $type = 'Otro';
    }
    $row['status'] = $entity->get('status')->getValue()[0]['value'];
    $row['type'] = $type;
    $row['send_quantity'] = $entity->get('send_quantity')->getString();
    $row['accepted_quantity'] = $entity->get('accepted_quantity')->getString();
    $row['weight'] = $entity->get('weight')->getString();
    return $row + parent::buildRow($entity);
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort('weight');

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

}
