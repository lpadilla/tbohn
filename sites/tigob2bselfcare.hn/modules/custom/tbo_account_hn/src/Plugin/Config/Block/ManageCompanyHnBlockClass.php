<?php

namespace Drupal\tbo_account_hn\Plugin\Config\Block;

use Drupal\tbo_account_hn\Plugin\Block\ManageCompanyHnBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

use Drupal\tbo_account\Plugin\Config\Block\ManageCompanyBlockClass;
use Drupal\tbo_account;

/**
 * Manage config a 'ManageCompanyHnBlockClass' block.
 */
class ManageCompanyHnBlockClass extends ManageCompanyBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param ManageCompanyHnBlock $instance
   * @param $config
   */
  public function setConfig(ManageCompanyHnBlock &$instance, &$config) {
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
            'title' => t('RTN'),
            'label' => 'Número de documento (RTN)',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 1,
            'class' => '3-columns',
            'validate_length' => 145,
          ],
          'name' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'name',
            'show' => 1,
            'weight' => 2,
            'class' => '3-columns',
            'validate_length' => 200,
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'document_number' => [
            'title' => t('RTN'),
            'label' => 'RTN',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 1,
          ],
          'name' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'name',
            'show' => 1,
            'weight' => 2,
          ],
          'status' => [
            'title' => t('Activo'),
            'label' => 'Activo',
            'service_field' => 'status',
            'show' => 1,
            'weight' => 3,
          ],
          'delete' => [
            'title' => t('Eliminar'),
            'label' => 'Eliminar',
            'service_field' => 'delete',
            'show' => 1,
            'weight' => 4,
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
  public function blockForm($form, FormStateInterface $form_state) {
    $form = $this->instance->cardBlockForm();
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function build(ManageCompanyHnBlock &$instance, &$config) {
    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;
   
    //Set data uuid, filters_fields, table_fields
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('directive', 'data-ng-companies-manage');
    $this->instance->setValue('config_name', 'companiesManageBlock');
    $this->instance->setValue('class', 'wrapper-create block-manage-companies');
       
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
    //Set session var
    $this->instance->cardBuildSession();

    //Se construye la variable $build con los datos que se necesitan en el tema
    $parameters = [
      'theme' => 'manage_company_hn',
      'library' => 'tbo_account_hn/companies-manage-hn',

    ];

    //set title
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    //Parameter additional
    $others = [
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],  
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/account/manage?_format=json');

    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'companiesManageBlock');

    //Se guarda el log de auditoria $event_type, $description, $details = NULL
    $this->instance->cardSaveAuditLog('Cuenta', 'Consulta listado de empresas', 'consultó listado de empresas');

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
