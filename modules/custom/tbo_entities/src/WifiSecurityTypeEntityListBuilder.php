<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Wifi security type entity entities.
 *
 * @ingroup tbo_entities
 */
class WifiSecurityTypeEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Wifi security type entity ID');
    $header['name'] = $this->t('Name');
    $header['keyword'] = $this->t('Keyword');
    $header['display_order'] = $this->t('Display Order');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.wifi_security_type_entity.edit_form',
      ['wifi_security_type_entity' => $entity->id()]
    );
    $row['keyword'] = $entity->keyword->value;
    $row['display_order'] = $entity->display_order->value;
    return $row + parent::buildRow($entity);
  }

}
