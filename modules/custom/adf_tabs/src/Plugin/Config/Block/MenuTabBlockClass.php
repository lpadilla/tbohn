<?php

namespace Drupal\adf_tabs\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\adf_tabs\Plugin\Block\MenuTabBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'MenuTabBlock' block.
 */
class MenuTabBlockClass {

  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\adf_tabs\Plugin\Block\MenuTabBlock $instance
   * @param $config
   */
  public function setConfig(MenuTabBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, &$form_state) {
    $form = $this->instance->cardBlockForm();
    $service = \Drupal::service('adf_tabs.menu_tab_service');
    $options = $service->optionsMenu();

    $block_service = \Drupal::service('adf_tabs.repository');

    $form['table_menu'] = [
      '#type' => 'table',
      '#header' => [t('Menu'), t('Show'), ''],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    foreach ($options as $option) {

      // Get blocks per menu configuration.
      $menus_blocks[$option['id']] = $block_service->itemsByIdMenu($option['id'], '');

      $form['table_menu'][$option['id']]['name'] = [
        '#type' => 'details',
        '#title' => $option['label'],
        '#open' => TRUE,
      ];

      $form['table_menu'][$option['id']]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => isset($this->configuration['menus'][$option['id']]['show']) ? $this->configuration['menus'][$option['id']]['show'] : 0,
      ];

      $form['table_menu'][$option['id']]['name']['table_fields'] = [
        '#type' => 'table',
        '#header' => [t('Bloque'), t('Texto de retorno móvil'), t('Show')],
        '#empty' => t('There are no items yet. Add an item.'),
        '#tabledrag' => [
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'fields-order-weight',
      ],
        ],
      ];
    }

    foreach ($menus_blocks as $menu_id => $blocks) {
      foreach ($blocks as $key => $block) {
        $form['table_menu'][$menu_id]['name']['table_fields'][$block->name] = [
          'label' => [
            '#plain_text' => $block->name,
          ],
          'return_text' => [
            '#type' => 'textfield',
            '#default_value' => isset($this->configuration['menus'][$menu_id]['name']['table_fields'][$block->name]['return_text']) ? $this->configuration['menus'][$menu_id]['name']['table_fields'][$block->name]['return_text'] : '',
          ],
          'show' => [
            '#type' => 'checkbox',
            '#default_value' => isset($this->configuration['menus'][$menu_id]['name']['table_fields'][$block->name]['show']) ? $this->configuration['menus'][$menu_id]['name']['table_fields'][$block->name]['show'] : '',
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['menus'] = $form_state->getValue('table_menu');
  }

  /**
   * {@inheritdoc}
   */
  public function build(MenuTabBlock &$instance, &$config) {

    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;
    $this->instance->cardBuildHeader(FALSE, FALSE);

    $this->instance->setValue('config_name', 'menuTabBlock');
    $this->instance->setValue('directive', 'data-ng-menu-tab');
    $this->instance->setValue('class', 'transactionCategoryBlock');
    // $options = $this->configuration['options'];.
    $service = \Drupal::service('adf_tabs.menu_tab_service');
    $options = $service->optionsMenu();
    $category = 'empty';

    if (isset($_SESSION['serviceDetail']['productId'])) {
      $category = $service->categoryByParameter($_SESSION['serviceDetail']['productId']);
      $category = $category->get('label');
    }
    $menuIds = [];
    foreach ($options as $option) {
      if ($this->configuration['menus'][$option['id']]['show'] == 1) {
        $menuIds[] = $option['id'];
      }
    }

    $result = $service->menuItems($menuIds, $category);

    foreach ($result[0] as $value) {
      $key = array_keys($value);
      foreach ($key as $k) {

        foreach ($menuIds as $menuId) {
          if ($this->configuration['menus'][$menuId]['name']['table_fields'][$value[$k]['label']]['show'] != 0) {
            $items[$k]['label'] = $value[$k]['label'];
            $items[$k]['return_text'] = $this->configuration['menus'][$menuId]['name']['table_fields'][$value[$k]['label']]['return_text'];
          }
        }
      }
    }

    foreach ($result[1] as $value) {

      $key = array_keys($value);
      foreach ($key as $k) {
        if (array_key_exists($k, $items)) {
          $blocks[$k] = $value[$k];
        }
      }

    }

    $parameters = [
      'library' => 'adf_tabs/card_menu_tab_library',
      'theme' => 'card_menu_tab',
    ];

    $keys = array_keys($items);
    $others = [
      '#directive' => $this->instance->getValue('directive'),
      '#blocks' => $blocks,
      '#items' => $items,
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);
    $other_config = ['currentView' => $keys[0]];
    $config_block = $this->instance->cardBuildConfigBlock('/adf_tabs/menu-tab?_format=json', $other_config);
    $this->instance->cardBuildAddConfigDirective($config_block);
    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $roles = $account->getRoles();
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
