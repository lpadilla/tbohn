<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\tbo_services\Plugin\Block\ChangeWifiNetNameBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'ChangeWifiNetNameBlockClass' block.
 */
class ChangeWifiNetNameBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function setConfig(ChangeWifiNetNameBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'table_options' => [
        'table_fields' => [
          'block_title' => [
            'input_type' => 'title',
            'title' => t('Título'),
            'label' => t('Realice el cambio del nombre de su red wifi'),
            'service_field' => 'block_title',
            'class' => '4-columns',
            'disabled' => TRUE,
            'show' => TRUE,
            'weight' => 1,
          ],
          'netname' => [
            'input_type' => 'text',
            'title' => t('Nombre'),
            'label' => t('Nombre'),
            'service_field' => 'netname',
            'identifier' => 'netname',
            'class' => '6-columns',
            'disabled' => TRUE,
            'show' => TRUE,
            'weight' => 2,
            'field_text_to_confirm' => [
              'with_status' => [
                'id' => 'status_netname',
                'angular' => '{[{ status_netname }]}',
              ],
              'ng_component' => [
                'ng' => 'ng-trim',
                'value' => 'false',
              ],
            ],
          ],
          'netname_confirm' => [
            'input_type' => 'text',
            'title' => t('confirmar nombre'),
            'label' => t('Confirme el nuevo nombre de su red wifi'),
            'service_field' => 'netname_confirm',
            'identifier' => 'netname_confirm',
            'class' => '6-columns',
            'disabled' => TRUE,
            'show' => TRUE,
            'weight' => 3,
            'field_text_to_confirm' => [
              'with_status' => [
                'id' => 'status_netname_confirm',
                'angular' => '{[{ status_netname_confirm }]}',
              ],
              'ng_component' => [
                'ng' => 'ng-trim',
                'value' => 'false',
              ],
            ],
          ],
          'description' => [
            'input_type' => 'label',
            'title' => t('Descripción'),
            'label' => t('El nuevo nombre debe incluir únicamente letras y números, no usar caracteres especiales como: ()!-*[{¿¿’&%#$#'),
            'service_field' => 'description',
            'class' => '12-columns',
            'show' => TRUE,
            'weight' => 4,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('Botón cambiar nombre de red wifi'),
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
    $form['table_options']['table_fields']['#header'] = [
      t('Title'),
      t('Label'),
      t('Show'),
      t('Weight'),
    ];

    $fields = [
      'block_title',
      'netname',
      'netname_confirm',
      'description',
    ];

    $form['buttons']['table_fields']['change']['active']['#disabled'] = TRUE;

    foreach ($fields as $key => $field) {
      unset($form['table_options']['table_fields'][$field]['class']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ChangeWifiNetNameBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'ChangeWifiNetNameBlock');
    $this->instance->setValue('directive', 'data-ng-change-wifi-net-name');
    $this->instance->setValue('class', 'block-ChangeWifiNetNameBlock');

    $parameters = [
      'theme' => 'change_wifi_net_name',
      'library' => 'tbo_services/change_wifi_net_name',
    ];

    $others = [
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title' => [
        'label' => $this->configuration['label'],
        'label_display' => $this->configuration['label_display'],
      ],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/change-wifi-net-name?_format=json');

    $this->instance->cardBuildAddConfigDirective($config_block);

    $build = $this->instance->getValue('build');
    $build['#fields'] = $this->configuration['table_options']['table_fields'];

    return $build;
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
