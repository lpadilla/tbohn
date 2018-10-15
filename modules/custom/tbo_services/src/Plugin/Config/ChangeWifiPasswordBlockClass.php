<?php

namespace Drupal\tbo_services\Plugin\Config;

use Drupal\tbo_services\Plugin\Block\ChangeWifiPasswordBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'ChangeWifiPasswordBlock' block.
 */
class ChangeWifiPasswordBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_services\Plugin\Block\ChangeWifiPasswordBlock $instance
   * @param $config
   */
  public function setConfig(ChangeWifiPasswordBlock &$instance, &$config) {
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
            'title' => t('Título'),
            'label' => t('Realice el cambio de su contraseña WiFi'),
            'show' => 1,
            'service_field' => 'block_title',
            'weight' => 1,
            'class' => '4-columns',
            'disabled' => TRUE,
            'input_type' => 'title',
          ],
          'password_to_confirm' => [
            'title' => t('Contraseña'),
            'input_type' => 'password',
            'label' => t('Contraseña'),
            'show' => 1,
            'service_field' => 'password',
            'identifier' => 'password',
            'weight' => 2,
            'max_length' => 13,
            'class' => '6-columns',
            'disabled' => TRUE,
            'password_to_confirm' => [
              'with_progress' => [
                'class' => 'pass-with-confirm',
                'style' => '',
              ],
              'with_icon' => [
                'icon' => 'icon-hide-cyan',
                'ng' => 'ng-click',
                'value' => 'showHide()',
                'size' => 'small',
                'id' => 'show_hide',
              ],
              'with_status' => [
                'id' => 'status_pass',
                'angular' => '{[{ status_pass }]}',
              ],
              'with_pass_force' => 1,
              'ng_component' => [
                'ng' => 'ng-change',
                'value' => 'validateStatus(password,password_confirm)',
              ],
              'icon_left' => 1,
            ],
          ],
          'password_confirm' => [
            'title' => t('confirmar contraseña'),
            'input_type' => 'password',
            'label' => t('Confirme su nueva contraseña'),
            'show' => 1,
            'service_field' => 'password_confirm',
            'identifier' => 'password_confirm',
            'weight' => 3,
            'max_length' => 13,
            'class' => '6-columns',
            'disabled' => TRUE,
            'password_to_confirm' => [
              'with_status' => [
                'id' => 'val_status',
                'angular' => '{[{ val_status }]}',
              ],
              'ng_component' => [
                'ng' => 'ng-change',
                'value' => 'validatePass(password,password_confirm)',
              ],
              'icon_left' => 1,
            ],
          ],
          'description' => [
            'title' => t('Descripción'),
            'label' => t('Tu nueva contraseña debe incluir únicamente letras y números, no usar caracteres especiales como: ()!-*[{¿¿’&%#$#'),
            'show' => 1,
            'service_field' => 'description',
            'weight' => 4,
            'input_type' => 'label',
            'class' => '12-columns',
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('Botón cambiar contraseña'),
            'label' => t('Cambiar'),
            'service_field' => 'action_card_change',
            'show' => 1,
            'active' => 0,
          ],
          'cancel' => [
            'title' => t('Botón cancelar'),
            'label' => t('Cancelar'),
            'service_field' => 'action_card_cancel',
            'show' => 1,
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
    $form['table_options']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'), t('Weight')];
    $fields = ['block_title', 'password_to_confirm', 'password_confirm', 'description'];

    $form['buttons']['table_fields']['change']['active']['#disabled'] = TRUE;

    foreach ($fields as $key => $field) {
      unset($form['table_options']['table_fields'][$field]['class']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ChangeWifiPasswordBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'changeWifiPasswordBlock');
    $this->instance->setValue('directive', 'data-ng-change-wifi-pass');
    $this->instance->setValue('class', 'block-changeWifiPasswordBlock');

    $parameters = [
      'theme' => 'change_wifi_pass',
      'library' => 'tbo_services/change_wifi_pass',
    ];

    $others = [
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title' => [
        'label' => $this->configuration['label'],
        'label_display' => $this->configuration['label_display'],
      ],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/change-wifi-pass?_format=json');

    $this->instance->cardBuildAddConfigDirective($config_block);

    $build = $this->instance->getValue('build');
    $build['#fields']['password_to_confirm'] = $this->configuration['table_options']['table_fields']['password_to_confirm'];
    $build['#fields']['password_confirm'] = $this->configuration['table_options']['table_fields']['password_confirm'];
    $build['#fields']['description'] = $this->configuration['table_options']['table_fields']['description'];

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
