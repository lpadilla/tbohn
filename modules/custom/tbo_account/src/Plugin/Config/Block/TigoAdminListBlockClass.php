<?php

namespace Drupal\tbo_account\Plugin\Config\Block;

use Drupal\tbo_account\Plugin\Block\TigoAdminListBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'TigoAdminListBlockClass' block.
 */
class TigoAdminListBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_account\Plugin\Block\TigoAdminListBlock $instance
   * @param $config
   */
  public function setConfig(TigoAdminListBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [
          'full_name' => ['title' => t('Nombres'), 'label' => 'Nombres', 'service_field' => 'full_name', 'show' => 1, 'weight' => 1, 'class' => '3-columns'],
          'mail' => ['title' => t('Correo electrónico'), 'label' => 'Correo electrónico', 'service_field' => 'mail', 'show' => 1, 'weight' => 2, 'class' => '3-columns'],
          'status' => ['title' => t('Estado'), 'label' => 'Estado', 'service_field' => 'status', 'show' => 1, 'weight' => 3, 'class' => '3-columns'],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'full_name' => ['title' => t('Nombres'), 'label' => t('Nombres'), 'type' => 'user', 'service_field' => 'full_name', 'show' => 1, 'weight' => 1],
          'mail' => ['title' => t('Correo electrónico'), 'label' => t('Correo electrónico'), 'type' => 'user', 'service_field' => 'mail', 'show' => 1, 'weight' => 2],
          'companies' => ['title' => t('Empresas'), 'label' => t('Empresas'), 'type' => 'company', 'service_field' => 'companies', 'show' => 1, 'weight' => 3],
          'status' => ['title' => t('Estado'), 'label' => t('Estado'), 'type' => 'user', 'service_field' => 'status', 'show' => 1, 'weight' => 4],
          'assign_enterprise' => ['title' => t('Asignar empresa'), 'label' => t('Asignar empresa'), 'type' => 'company', 'service_field' => 'assign_enterprise', 'show' => 1, 'weight' => 5],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [
            'number_pages' => 10,
            'number_rows_pages' => 10,
          ],
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'url_config' => 'reasignar-empresas',
        ],
      ],
      'not_show_class' => [
        'columns' => 1,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $field['url_config'] = [
      '#type' => 'textfield',
      '#title' => t('Url reasignar empresas'),
      '#description' => 'Ingrese la Url para reasignar empresa, por ejemplo reasignar-empresas',
      '#default_value' => $this->configuration['others']['config']['url_config'],
      '#required' => TRUE,
      '#size' => 64,
    ];

    $form = $this->instance->cardBlockForm($field);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(TigoAdminListBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'TigoAdminListBlock');
    $this->instance->setValue('directive', 'data-ng-manage-users-tigo-admin');
    $this->instance->setValue('class', 'user-tigotbo manage-users-tigo-admin');

    // Ordering table_fields.
    $this->instance->ordering('filters_fields', 'filters_options');

    // Set filters configurations.
    $filters = [];
    foreach ($this->instance->getValue('filters_fields') as $key => $value) {
      if ($value['show'] == 1) {
        $filters[$key]['identifier'] = $key;
        $filters[$key]['label'] = $value['label'];
        $filters[$key]['service_field'] = $value['service_field'];
        $class = ['field-' . $value['service_field'], $value['class']];
        $filters[$key]['class'] = implode(" ", $class);

        if ($value['service_field'] == 'full_name') {
          $filters[$key]['validate_length'] = 300;
        }
        elseif ($value['service_field'] == 'mail') {
          $filters[$key]['validate_length'] = 200;
        }

        if ($key == 'status') {
          $filters[$key]['type'] = 'select';
          $filters[$key]['none'] = 'Seleccionar';
          $filters[$key]['options'] = [1 => 'Activo', 0 => 'Inactivo'];
        }

        if ($key == 'mail') {
          $filters[$key]['input_type'] = 'email';
        }
      }
    }

    // Set filters.
    $this->instance->setValue('filters', $filters);

    // Ordering table_fields.
    $this->instance->ordering('table_fields', 'table_options');

    $data = [];
    foreach ($this->instance->getValue('table_fields') as $key => $value) {
      if ($value['show'] == 1) {
        $data[$key]['identifier'] = $key;
        $data[$key]['label'] = $value['label'];
        $data[$key]['service_field'] = $value['service_field'];
        $data[$key]['type'] = $value['type'];
        $class = ['field-' . $value['service_field'], $value['class']];
        $data[$key]['class'] = implode(" ", $class);
      }
    }

    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account\Form\CreateUsersForm');

    // Set title.
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    $build = [
      '#theme' => 'tigo_admin_list',
      '#uuid' => $this->instance->getValue('uuid'),
      '#config' => $this->configuration,
      '#filters' => $filters,
      '#fields' => $data,
      '#form' => $form,
      '#directive' => $this->instance->getValue('directive'),
      '#class' => $this->instance->getValue('class'),
      '#modal' => [
        'href' => 'modalFormEnterprise',
        'label' => t('Crear Usuario'),
      ],
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#url_config' => $this->configuration['others']['config']['url_config'],
      '#attached' => [
        'library' => [
          'tbo_account/tigoAd-list',
        ],
      ],
      '#plugin_id' => $this->instance->getPluginId(),
    ];

    // Set columns and headers_table_query.
    $this->instance->setValue('build', $build);

    // Set data to directive.
    $other_config = [
      'fields' => $data,
    ];

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/api/tigo-admin-list?_format=json', $other_config);

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'TigoAdminListBlock');

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
    if (in_array('administrator', $roles) || in_array('super_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }

}
