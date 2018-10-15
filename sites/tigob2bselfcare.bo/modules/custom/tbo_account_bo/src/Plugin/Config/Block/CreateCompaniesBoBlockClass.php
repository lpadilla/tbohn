<?php

namespace Drupal\tbo_account_bo\Plugin\Config\Block;

use Drupal\tbo_account_bo\Plugin\Block\CreateCompaniesBoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_account\Plugin\Config\Block\CreateCompaniesBlockClass;
use Drupal\tbo_account;

/**
 * Manage config a 'CategoryServicesListBlock' block.
 */
class CreateCompaniesBoBlockClass extends CreateCompaniesBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param CreateCompaniesBoBlock $instance
   * @param $config
   */
  public function setConfig(CreateCompaniesBoBlock &$instance, &$config) {
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
          'document_number' => [
            'title' => t('NIT'),
            'label' => 'Número',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 1,
            'class' => '3-columns',
            'validate_length' => 145,
          ],
          'name' => [
            'title' => t('Empresa'),
            'label' => 'Nombre Empresa',
            'service_field' => 'name',
            'show' => 1,
            'weight' => 2,
            'class' => '3-columns',
            'validate_length' => 200,
            'autocomplete' => TRUE,
          ],
          'client_code' => [
            'title' => t('Código de Cliente'),
            'label' => 'Código de Cliente',
            'service_field' => 'client_code',
            'show' => 0,
            'weight' => 6,
            'class' => '1-columns', 
            'validate_length' => 300
          ],
          'user_name' => [
            'title' => t('Admin empresa'),
            'label' => 'Nombres y Apellidos',
            'service_field' => 'user_name',
            'show' => 1,
            'weight' => 3,
            'class' => '3-columns',
            'validate_length' => 300,
          ],
          'mail' => [
            'title' => t('Correo electrónico'),
            'label' => 'ejemplo@tigo.com',
            'service_field' => 'mail',
            'show' => 1,
            'weight' => 4,
            'class' => '3-columns',
            'validate_length' => 200,
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'document_number' => [
            'title' => t('NIT / Número'),
            'label' => 'NIT / Número',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 1,
          ],
          'document_type' => [
            'title' => t('Tipo de documento'),
            'label' => 'Tipo de documento',
            'service_field' => 'document_type',
            'show' => 1,
            'weight' => 2,
          ],
          'name' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'name',
            'show' => 1,
            'weight' => 3,
          ],
          'segment' => [
            'title' => t('Segmento'),
            'label' => 'Segmento',
            'service_field' => 'segment',
            'show' => 1,
            'weight' => 4,
          ],
          'user_name' => [
            'title' => t('Admin empresa'),
            'label' => 'Admin empresa',
            'service_field' => 'user_name',
            'show' => 1,
            'weight' => 5,
          ],
          'status' => [
            'title' => t('Estado'),
            'label' => 'Estado',
            'service_field' => 'status',
            'show' => 1,
            'weight' => 6,
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

  public function build() {
    // Set data uuid, filters_fields, table_fields.
    $this->instance->cardBuildHeader(TRUE, FALSE);

    $this->instance->setValue('directive', 'data-ng-companies-list');
    $this->instance->setValue('config_name', 'companiesListBlock');
    $this->instance->setValue('class', 'wrapper-companies block-create-company-message');
    $filters_fields = $this->configuration['filters_fields'];
    // Si se construye una logica propia para los filtros, se debe setear el valor de los filtros con $this->setFilters($filters)
    $filters = array();
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        //Solo permitir acceso a roles tigo_admin y super_admin del filtro segmento
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $filters[$key_filter]['placeholder'] = isset($filter['label']) ? $filter['label']:'';
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        if (isset($filter['validations'])) {
          $filters[$key_filter]['validations'] = $filter['validations'];
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
    
    $this->instance->setFilters($filters);

    // Si se construye logica propia para las columnas, se debe utilizar el metodo $this->setColumns($columns, $headers_table_query)
    $columns = [];
    $headers_table_query = [];
    $company_fields = [
      'document_number' => 'document_number',
      'document_type' => 'document_type',
      'name' => 'name',
      'segment' => 'segment',
    ];

    // Ordering table_fields.
    $this->instance->ordering('table_fields', 'table_options');
    // Recorremos $table_fields para verificar las columnas a mostrar.
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $columns[$key_field]['key'] = $key_field;
        $columns[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $columns[$key_field]['class'] = implode(" ", $classes);
        $columns[$key_field]['service_field'] = $field['service_field'];

        if (array_key_exists($key_field, $company_fields)) {
          $headers_table_query[$key_field]['value'] = $key_field;
          $headers_table_query[$key_field]['type'] = 'company';
        }
        else {
          if ($key_field == 'user_name') {
            $headers_table_query[$key_field]['value'] = 'full_name';
          }
          else {
            $headers_table_query[$key_field]['value'] = $key_field;
          }
          $headers_table_query[$key_field]['type'] = 'user';
        }
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Set columns and headers_table_query.
    $this->instance->setColumns($columns, $headers_table_query);

    // Set session var.
    $this->instance->cardBuildSession();

    // Get form enterprise.
    $form = $this->instance->cardBuildGetForm('\Drupal\tbo_account_bo\Form\CreateCompanyForm');

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'companies_list_bo',
      'library' => 'tbo_account_bo/companies-list-bo',
      'columns' => $columns,
    ];

    // Set title.
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    // Parameter additional.
    $others = [
      '#form' => $form,
      '#modal' => [
        'href' => 'modalFormEnterprise',
        'label' => 'Crear Empresa',
      ],
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/account/list?_format=json');

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block);

    // Se guarda el log de auditoria $event_type, $description, $details = NULL.
    $this->instance->cardSaveAuditLog('Cuenta', 'Usuario accede a activacion de empresas', 'consulta activación de empresas');

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
    if (in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }


}




