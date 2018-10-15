<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\tbo_services\Plugin\Block\ChangeWifiChannelBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'ChangeWifiChannelBlock' block.
 */
class ChangeWifiChannelBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * Set Config.
   *
   * @param \Drupal\tbo_services\Plugin\Block\ChangeWifiChannelBlock $instance
   *   Instance of ChangeWifiChannelBlock.
   * @param array $config
   *   Configuration object.
   */
  public function setConfig(ChangeWifiChannelBlock &$instance, array &$config = []) {
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
            'title' => t('Título'),
            'label' => t('Acá podrá realizar el cambio de canal'),
            'service_field' => 'block_title',
            'show' => TRUE,
          ],
          'wifi_channel' => [
            'title' => t('Nuevo canal de red WiFi'),
            'label' => t('Nuevo canal de tu red WiFi'),
            'service_field' => 'wifi_channel',
            'input_type' => 'select',
            'identifier' => 'wifi_channel',
            'show' => TRUE,
            'none' => 'Selecciona un canal de red WiFi',
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('Botón cambiar red WiFi'),
            'label' => t('Cambiar'),
            'service_field' => 'action_card_change_wifi_channel',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
          ],
          'cancel' => [
            'title' => t('Botón cancelar'),
            'label' => t('Cancelar'),
            'service_field' => 'action_card_cancel_change_wifi_channel',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
          ],
        ],
      ],
      'label_display' => '0',
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_card' => 1,
          ],
        ],
      ],
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
    $values = ['change', 'cancel'];
    foreach ($values as $value) {
      unset($form['buttons']['table_fields'][$value]['url']);
    }

    // Set container name.
    $form['others_display']['#title'] = t('Configuraciones de campos del card');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ChangeWifiChannelBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'changeWifiChannelBlock');
    $this->instance->setValue('directive', 'data-ng-change-wifi-channel');
    $this->instance->setValue('class', 'block-changeWifiChannelBlock');

    // We get the WiFi Channels List from the Content Entity "Wifi Channel".
    $tboEntities = \Drupal::service('tbo_entities.entities_service');
    $wifiChannels = $tboEntities->getWifiChannels();
    $optionsWifiChannels = [];
    foreach ($wifiChannels as $key => $data) {
      $optionsWifiChannels[$data['keyword']] = $data['name'];
    }

    $others_display = $this->configuration['others_display']['table_fields'];
    $others_display['wifi_channel']['options'] = $optionsWifiChannels;

    // Set theme $vars.
    $parameters = [
      'theme' => 'change_wifi_channel',
      'library' => 'tbo_services/change_wifi_channel',
    ];

    $others = [
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title' => [
        'label' => $this->configuration['label'],
        'label_display' => $this->configuration['label_display'],
      ],
      '#fields' => $others_display,
      '#wifi_channels' => $wifiChannels,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Set JavaScript data.
    $other_params = [
      'pop_fields' => $this->configuration['table_options']['table_fields'],
    ];
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/change-wifi-channel?_format=json', $other_params);

    // We inject the configuration into the JS Drupal object.
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

    if (in_array('administrator', $roles) ||
      in_array('admin_company', $roles) ||
      in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
