<?php

namespace Drupal\tbo_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Wifi channel entities.
 *
 * @ingroup tbo_entities
 */
class WifiChannelListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Wifi channel ID');
    $header['name'] = $this->t('Name');
    $header['keyword'] = $this->t('Keyword');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tbo_entities\Entity\WifiChannel */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.wifi_channel.edit_form',
      ['wifi_channel' => $entity->id()]
    );
    $row['keyword'] = $entity->keyword->value;

    return $row + parent::buildRow($entity);
  }

}
