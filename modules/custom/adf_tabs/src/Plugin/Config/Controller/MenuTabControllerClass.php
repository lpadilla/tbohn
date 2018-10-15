<?php

namespace Drupal\adf_tabs\Plugin\Config\Controller;

/**
 *
 */
class MenuTabControllerClass {

  /**
   * @param $idBlock
   *   id del bloque
   *
   * @return array
   */
  public function mobileViewBlock($idBlock) {
    $service = \Drupal::service('adf_tabs.repository');
    $item = $service->configBlockById($idBlock);
    $settings = $item[0]->block_config;
    $block = \Drupal::service('adf_tabs.menu_tab_service')->renderBlock($item[0]->block_id, $settings);
    return [
      '#theme' => 'card_menu_tab_movil',
      '#block' => $block,
    ];
  }

}
