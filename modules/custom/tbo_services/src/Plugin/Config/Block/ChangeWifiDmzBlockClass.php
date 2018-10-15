<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_services\Plugin\Block\ChangeWifiDmzBlock;

/**
 * Manage config a 'ChangeWifiDmzBlockClass' block.
 */
class ChangeWifiDmzBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Implement of setConfig.
   *
   * @param \Drupal\tbo_services\Plugin\Block\ChangeWifiDmzBlock $instance
   *   Instance.
   * @param array|object $config
   *   Config.
   */
  public function setConfig(ChangeWifiDmzBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'others_display' => [
        'table_fields' => [
          'block_title' => [
            'input_type' => 'title',
            'title' => t('Título'),
            'label' => t('En este espacio podrá configurar el DMZ'),
            'service_field' => 'block_title',
            'show' => TRUE,
            'weight' => 1,
          ],
          'wifi_dmz' => [
            'input_type' => 'text',
            'title' => t('Agregue un DMZ para su red'),
            'label' => t('Agregue un DMZ para su red'),
            'service_field' => 'wifi_dmz',
            'identifier' => 'wifi_dmz',
            'max_length' => 15,
            'show' => TRUE,
            'weight' => 2,
            'with_status' => [
              'id' => 'status_dmz',
              'angular' => '{[{ status_dmz }]}',
            ],
            'ng_component' => [
              'ng' => 'ng-trim',
              'value' => 'false',
            ],
          ],
          'description' => [
            'input_type' => 'label',
            'title' => t('Descripción'),
            'label' => t('El nuevo dmz debe incluir únicamente números y puntos, no usar letras, espacios ni caracteres especiales como: ()!-*[{¿¿’&%#$#'),
            'service_field' => 'description',
            'show' => TRUE,
            'weight' => 3,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('Botón cambiar dmz de red wifi'),
            'label' => t('Cambiar'),
            'service_field' => 'action_card_change',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => 0,
          ],
          'cancel' => [
            'title' => t('Botón cancelar'),
            'label' => t('Cancelar'),
            'service_field' => 'action_card_cancel',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => 1,
          ],
        ],
      ],
      'label_display' => '0',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();

    // Rebuild table headers.
    $form['table_options']['table_fields']['#header'] = [
      t('Title'),
      t('Label'),
      t('Show'),
      t('Weight'),
    ];

    // Unset class select of all fields.
    $fields = ['block_title', 'wifi_dmz', 'description'];

    foreach ($fields as $key => $field) {
      unset($form['table_options']['table_fields'][$field]['class']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ChangeWifiDmzBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'ChangeWifiDmzBlock');
    $this->instance->setValue('directive', 'data-ng-change-wifi-dmz');
    $this->instance->setValue('class', 'block-ChangeWifiDmzBlock');

    $parameters = [
      'theme' => 'change_wifi_dmz',
      'library' => 'tbo_services/change_wifi_dmz',
    ];

    $others = [
      '#title' => [
        'label' => $this->configuration['label'],
        'label_display' => $this->configuration['label_display'],
      ],
      '#fields' => $this->configuration['others_display']['table_fields'],
      '#buttons' => $this->configuration['buttons']['table_fields'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Set JavaScript data.
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/change-wifi-dmz?_format=json');

    $this->instance->cardBuildAddConfigDirective($config_block);

    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
