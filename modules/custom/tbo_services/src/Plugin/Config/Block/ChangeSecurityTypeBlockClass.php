<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_services\Plugin\Block\ChangeSecurityTypeBlock;

/**
 * Provides a 'ChangeSecurityTypeBlockClass' block.
 *
 * @Block(
 *  id = "change_security_type_block",
 *  admin_label = @Translation("Change security type block"),
 * )
 */
class ChangeSecurityTypeBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function setConfig(ChangeSecurityTypeBlock &$instance, &$config) {
    $this->instance = $instance;
    $this->configuration = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'others_display' => [
        'table_fields' => [
          'block_title' => [
            'title' => t('Título del Bloque'),
            'label' => t('Realice el cambio de tipo de seguridad WiFi'),
            'show' => 1,
            'service_field' => 'block_title',
            'weight' => 1,
            'class' => '4-columns',
            'disabled' => TRUE,
            'input_type' => 'title',
          ],
          'security_type' => [
            'title' => t('Cambiar tipo de seguridad'),
            'label' => t('Selecciona el método de encriptación para tu red WiFi'),
            'service_field' => 'security_type',
            'input_type' => 'select',
            'identifier' => 'security_type',
            'show' => TRUE,
            'default_value' => 'WPA',
          ],
          'new_password' => [
            'title' => t('Nueva contraseña'),
            'input_type' => 'password',
            'label' => t('Ingresa una nueva contraseña para tu red WiFi'),
            'show' => 1,
            'service_field' => 'new_password',
            'identifier' => 'new_password',
            'weight' => 3,
            'max_length' => 64,
            'class' => '6-columns',
            'regex' => TRUE,
            'disabled' => TRUE,
            'password_to_confirm' => [
              'with_progress' => [
                'class' => 'new-password',
                'style' => '',
              ],
              'with_status' => [
                'id' => 'status_new_password',
                'angular' => '{[{ status_new_password }]}',
              ],
              'with_pass_force' => 1,
              'ng_component' => [
                'ng' => 'ng-change',
                'value' => 'validatePassword(new_password, security_type)',
              ],
              'icon_left' => 0,
            ],
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('Botón Cambiar'),
            'service_field' => 'action_card_change_wifi_security_type',
            'label' => t('Cambiar'),
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => FALSE,
          ],
          'cancel' => [
            'title' => t('Botón Cancelar'),
            'label' => t('Cancelar'),
            'service_field' => 'action_card_cancel_wifi_security_type',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
          ],
        ],
      ],
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();

    // Rebuild table headers.
    $form['others_display']['table_fields']['#header'] = [
      t('Title'),
      t('Label'),
      t('Show'),
    ];
    $form['buttons']['table_fields']['#header'] = [
      t('Title'),
      t('Label'),
      t('Show'),
      t('Active'),
    ];

    // We configure the buttons display.
    $buttonsList = ['change', 'cancel'];
    foreach ($buttonsList as $buttonName) {
      unset($form['buttons']['table_fields'][$buttonName]['url']);
    }

    // Set container name.
    $form['others_display']['#title'] = t('Configuraciones de campos del card');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ChangeSecurityTypeBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'changeSecurityTypeBlock');
    $this->instance->setValue('directive', 'data-ng-change-security-type');
    $this->instance->setValue('class', 'block-changeSecurityTypeBlock');

    // We get the WiFi Security Types List from the
    // Content Entity "Wifi Security Type".
    $tboEntities = \Drupal::service('tbo_entities.entities_service');
    $wifiSecurityTypes = $tboEntities->getWifiSecurityTypes();
    $optionsWifiSecurityTypes = [];
    $optionsWifiSecurityTypesFull = [];
    foreach ($wifiSecurityTypes as $data) {
      $optionsWifiSecurityTypes[$data['keyword']] = $data['name'];
      $optionsWifiSecurityTypesFull[] = $data;
    }

    $others_display = $this->configuration['others_display']['table_fields'];
    $others_display['security_type']['options'] = $optionsWifiSecurityTypes;
    $others_display['security_type']['options_full'] = $optionsWifiSecurityTypesFull;

    $others = [
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title' => [
        'label' => $this->configuration['label'],
        'label_display' => $this->configuration['label_display'],
      ],
      '#fields' => $others_display,
      '#pop_up_fields' => $others_display,
    ];

    $parameters = [
      'theme' => 'change_security_type',
      'library' => 'tbo_services/change_security_type',
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // We inject the configuration into the JS Drupal object.
    $other_params = [
      'pop_fields' => $others_display,
    ];
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/change-security-type?_format=json', $other_params);
    $this->instance->cardBuildAddConfigDirective($config_block, 'changeSecurityTypeBlock');

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

    if (in_array('administrator', $roles) ||
      in_array('admin_company', $roles) ||
      in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
