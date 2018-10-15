<?php

namespace Drupal\tbo_core\Plugin\Config\Block;

use Drupal\tbo_core\Plugin\Block\SystemLogsBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'SystemLogsBlockClass' block.
 */
class SystemLogsBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function setConfig(SystemLogsBlock &$instance, &$config) {
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
          'created' => [
            'title' => t("Fecha"),
            'label' => 'Fecha',
            'service_field' => 'created',
            'show' => 1,
            'weight' => 1,
            'class' => '5-columns',
          ],
          'company_name' => [
            'title' => t("Empresa/Cliente"),
            'label' => 'Empresa/Cliente',
            'service_field' => 'company_name',
            'show' => 1,
            'weight' => 2,
            'class' => '3-columns',
            'validate_length' => '200',
          ],
          'company_segment' => [
            'title' => t("Segmento"),
            'label' => 'Segmento',
            'service_field' => 'company_segment',
            'show' => 1,
            'weight' => 3,
            'class' => '3-columns',
            'validate_length' => '130',
          ],
          'user_names' => [
            'title' => t("Nombres"),
            'label' => 'Nombres',
            'service_field' => 'user_names',
            'show' => 1,
            'weight' => 4,
            'class' => '3-columns',
            'validate_length' => '300',
          ],
          'user_role' => [
            'title' => t("Tipo"),
            'label' => 'Tipo',
            'service_field' => 'user_role',
            'show' => 1,
            'weight' => 5,
            'class' => '3-columns',
          ],
          'description' => [
            'title' => t("Descripcion"),
            'label' => 'Descripcion',
            'service_field' => 'description',
            'show' => 1,
            'weight' => 6,
            'class' => '3-columns',
            'validate_length' => '300',
          ],
          'details' => [
            'title' => t("Detalles"),
            'label' => 'Detalles',
            'service_field' => 'details',
            'show' => 1,
            'weight' => 7,
            'class' => '3-columns',
            'validate_length' => '350',
          ],
          'old_values' => [
            'title' => t("Anterior"),
            'label' => 'Anterior',
            'service_field' => 'old_values',
            'show' => 1,
            'weight' => 8,
            'class' => '3-columns',
            'validate_length' => '130',
          ],
          'new_values' => [
            'title' => t("Nuevo"),
            'label' => 'Nuevo',
            'service_field' => 'new_values',
            'show' => 1,
            'weight' => 9,
            'class' => '1-columns',
            'validations' => 'maxlength=130',
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'created' => [
            'title' => t("Fecha"),
            'label' => 'Fecha',
            'service_field' => 'created',
            'show' => 1,
            'weight' => 1,
          ],
          'user_role' => [
            'title' => t("Tipo de usuario"),
            'label' => 'Tipo de usuario',
            'service_field' => 'user_role',
            'show' => 1,
            'weight' => 2,
          ],
          'company_name' => [
            'title' => t("Empresa/Cliente"),
            'label' => 'Empresa/Cliente',
            'service_field' => 'company_name',
            'show' => 1,
            'weight' => 3,
          ],
          'company_document_number' => [
            'title' => t("Nit"),
            'label' => 'Nit',
            'service_field' => 'company_document_number',
            'show' => 1,
            'weight' => 4,
          ],
          'company_segment' => [
            'title' => t("Segmento"),
            'label' => 'Segmento',
            'service_field' => 'company_segment',
            'show' => 1,
            'weight' => 4,
          ],
          'user_names' => [
            'title' => t("Nombres"),
            'label' => 'Nombres',
            'service_field' => 'user_names',
            'show' => 1,
            'weight' => 5,
          ],
          'event_type' => [
            'title' => t("Tipo"),
            'label' => 'Tipo',
            'service_field' => 'event_type',
            'show' => 1,
            'weight' => 6,
          ],
          'description' => [
            'title' => t("Descripcion"),
            'label' => 'Descripcion',
            'service_field' => 'description',
            'show' => 1,
            'weight' => 7,
          ],
          'details' => [
            'title' => t("Detalles"),
            'label' => 'Detalles',
            'service_field' => 'details',
            'show' => 1,
            'weight' => 8,
          ],
          'old_values' => [
            'title' => t("Anterior"),
            'label' => 'Anterior',
            'service_field' => 'old_values',
            'show' => 1,
            'weight' => 9,
          ],
          'new_values' => [
            'title' => t("Nuevo"),
            'label' => 'Nuevo',
            'service_field' => 'new_values',
            'show' => 1,
            'weight' => 10,
          ],
          'technical_detail' => [
            'title' => t("Detalle técnico"),
            'label' => 'Detalle técnico',
            'service_field' => 'technical_detail',
            'show' => 1,
            'weight' => 11,
          ],
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
          'options_date' => 10,
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
    // Range's date.
    $fields['options_date'] = [
      '#type' => 'textfield',
      '#title' => t('Escoja el rango en días para la consulta de las fechas'),
      '#default_value' => $this->configuration['others']['config']['options_date'],
      '#size' => 5,
      '#maxlength' => 5,
    ];

    $form = $this->instance->cardBlockForm($fields);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(SystemLogsBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'auditLogsBlock');
    $this->instance->setValue('directive', 'data-ng-audit-logs-list');
    $this->instance->setValue('class', 'wrapper-logs block-audit-logs');

    // Add fields to filters.
    $form['date_range']['date_start'] = [
      '#type' => 'date',
      '#title' => t('Desde'),
      '#attributes' => [
        'class' => ['datepicker'],
        'data-ng-model' => 'date_start',
        'placeholder' => t('mm/dd/aa'),
        'id' => 'date_start_log',
      ],
    ];

    $form['date_range']['date_end'] = [
      '#type' => 'date',
      '#title' => t('Hasta'),
      '#attributes' => [
        'class' => ['datepicker'],
        'data-ng-model' => 'date_end',
        'placeholder' => t('mm/dd/aa'),
        'id' => 'date_end_log',
      ],
    ];

    $user_roles = \Drupal::currentUser()->getRoles(TRUE);

    // Get Rol.
    $roles = user_role_names(TRUE);
    unset($roles['authenticated']);

    foreach ($user_roles as $key => $value) {
      if ($value == 'admin_company') {
        $roles = [
          'admin_company' => 'Admin Empresa',
          'admin_grupo' => 'Admin Grupo',
        ];
      }
      elseif ($value == 'admin_grupo') {
        $roles = [
          'admin_grupo' => 'Admin Grupo',
        ];
      }
      elseif ($value == 'super_admin') {
        $roles = [
          'super_admin' => 'Super Admin',
          'tigo_admin' => 'Tigo Admin',
          'admin_company' => 'Admin Empresa',
          'admin_grupo' => 'Admin Grupo',
        ];
      }
      elseif ($value == 'tigo_admin') {
        $roles = [
          'tigo_admin' => 'Tigo Admin',
          'admin_company' => 'Admin Empresa',
          'admin_grupo' => 'Admin Grupo',
        ];
      }
    }

    // Ordering table_fields.
    $this->instance->ordering('filters_fields', 'filters_options');

    // Set filters configurations.
    $filters = [];

    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        // Solo permitir acceso a roles tigo_admin y
        // super_admin del filtro segmento.
        if ($filter['service_field'] == 'company_segment') {
          if (!in_array('tigo_admin', $user_roles) && !in_array('super_admin', $user_roles) && !in_array('administrator', $user_roles)) {
            continue;
          }
        }

        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];

        if (!empty($filter['validate_length'])) {
          $filters[$key_filter]['validate_length'] = $filter['validate_length'];
        }

        if ($key_filter == 'created') {
          $filters[$key_filter]['created'] = $form['date_range'];
        }

        if ($key_filter == 'user_role') {
          $filters[$key_filter]['select_multiple'] = TRUE;
          $filters[$key_filter]['options'] = $roles;
        }
      }
    }
    // Set filters.
    $this->instance->setValue('filters', $filters);
    // Ordering table_fields.
    $this->instance->ordering('table_fields', 'table_options');
    // Init values.
    $data = $headers_table = [];
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        // Solo permitir acceso a roles tigo_admin y
        // super_admin del filtro segmento.
        if ($field['service_field'] == 'company_segment') {
          if (!in_array('tigo_admin', $user_roles) || !in_array('super_admin', $user_roles) || !in_array('administrator', $user_roles)) {
            continue;
          }
        }
        if ($field['service_field'] == 'technical_detail') {
          if (!in_array('super_admin', $user_roles) && !in_array('administrator', $user_roles)) {
            continue;
          }
        }
        $data[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $data[$key_field]['class'] = implode(" ", $classes);
        $data[$key_field]['service_field'] = $field['service_field'];

        // Add headers_table.
        $headers_table[$key_field]['value'] = $key_field;

        unset($classes);
      }
    }
    // Save data by export.
    $save_to_export = \Drupal::service('config.factory')->getEditable('tbo_export.audit')
      ->set($this->instance->getValue('uuid'), $data)->save();
    // Set columns and headers_table_query.
    $this->instance->setColumns($data, $headers_table);

    // Set session var.
    $this->instance->cardBuildSession();

    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account\Form\CreateUsersForm');

    // Se construye la variable $build con los datos que se
    // necesitan en el tema.
    $parameters = [
      'theme' => 'audit_logs',
      'library' => 'tbo_core/logs',
    ];

    // Set title.
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }
    // Parameter additional.
    $others = [
      '#options_date' => $this->configuration['others']['config']['options_date'],
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];
    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular,
    // se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/api/logs?_format=json');

    // Add another config.
    $others = [
      'options_date' => $this->configuration['others']['config']['options_date'],
    ];

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'auditLogsBlock', $others);

    // Setting options date range pickadate.
    $build = $this->instance->getValue('build');
    $build['#attached']['drupalSettings']['options_date'] = $others;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
