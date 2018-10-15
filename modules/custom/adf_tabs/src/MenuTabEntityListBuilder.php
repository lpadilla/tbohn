<?php

namespace Drupal\adf_tabs;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Menu tab entity entities.
 *
 * @ingroup adf_tabs
 */
class MenuTabEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Menu tab entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\adf_tabs\Entity\MenuTabEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.menu_tab_entity.edit_form',
      ['menu_tab_entity' => $entity->id()]
    );

    // Delete sesion menu items.
    unset($_SESSION['form_state_menu_tag']);
    unset($_SESSION['form_menu_tag']);
    unset($_SESSION['all_config_menu']);

    return $row + parent::buildRow($entity);
  }

}
