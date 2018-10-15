<?php

namespace Drupal\adf_tabs\Services;

/**
 *
 */
class MenuTabService {

  /**
   * Get all menu.
   *
   * @return array
   */
  public function optionsMenu() {
    $menus = [];
    $service = \Drupal::service('adf_tabs.repository');
    $menusObj = $service->optionsMenu();
    foreach ($menusObj as $m) {
      $menus[] = ['id' => $m->id, 'label' => $m->name];
    }
    return $menus;
  }

  /**
   * Get all category.
   *
   * @param $parameter
   *
   * @return mixed
   */
  public function categoryByParameter($parameter) {
    $service = \Drupal::service('adf_tabs.repository');
    return $service->categoryByParameter($parameter);
  }

  /**
   * Get all items on menu.
   *
   * @param $menuIds
   *   menu id
   * @param string $category
   *   category.
   *
   * @return array
   */
  public function menuItems($menuIds, $category = 'empty') {
    $items = [];
    $blocks = [];
    foreach ($menuIds as $id) {
      $result = $this->itemsByIdMenu($id, $category);
      $items[] = $result[0];
      $blocks[] = $result[1];
    }
    return [$items, $blocks];
  }

  /**
   * Get the menu item.
   *
   * @param $menuId
   *   menu id
   * @param $category
   *
   * @return array
   */
  public function itemsByIdMenu($menuId, $category) {
    $service = \Drupal::service('adf_tabs.repository');
    $itemsData = $service->itemsByIdMenu($menuId, $category);

    foreach ($itemsData as $item) {
      if ($item->category == 'empty' || $category == $item->category) {
        $items[$item->id]['label'] = $item->name;
        $block = $this->renderBlock($item->block_id, $item->block_config);
        $blocks[$item->id] = $block;

      }
    }
    return [$items, $blocks];
  }

  /**
   * Block to render.
   *
   * @param $blockId
   *   block id
   * @param $blockConfig
   *
   * @return mixed
   */
  public function renderBlock($blockId, $blockConfig) {
    $con = unserialize($blockConfig);
    $blockManager = \Drupal::service('plugin.manager.block');
    $pluginBlock = $blockManager->createInstance($blockId, $con);
    return $pluginBlock->build();
  }

  /**
   * Get all categories.
   *
   * @return array
   */
  public function optionsCategories() {
    $service = \Drupal::service('adf_tabs.repository');
    $categories = $service->allCategories('category_services_entity');

    $selectCategory = ['empty' => t('Seleccione')];
    foreach ($categories as $key => $entity) {
      $labelCategory = $entity->get('label');
      $selectCategory[$labelCategory] = $labelCategory;
    }
    return $selectCategory;
  }

  /**
   * @return array
   */
  public function getBlockOptions() {
    $block_manager = \Drupal::service('plugin.manager.block');
    $context_repository = \Drupal::service('context.repository');

    // Only add blocks which work without any available context.
    $definitions = $block_manager->getDefinitionsForContexts($context_repository->getAvailableContexts());
    // Order by category, and then by admin label.
    $definitions = $block_manager->getSortedDefinitions($definitions);

    $blocks = [];
    foreach ($definitions as $block_id => $definition) {
      $blocks[$block_id] = t('Módulo: ') . '"' . $definition['provider'] . '"' . ' - ' . t('Bloque: ') . '"' . $definition['admin_label'] . '"';
    }

    return $blocks;
  }

}
