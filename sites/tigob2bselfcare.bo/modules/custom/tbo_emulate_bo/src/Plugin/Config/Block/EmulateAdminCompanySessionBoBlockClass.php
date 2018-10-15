<?php

namespace Drupal\tbo_emulate_bo\Plugin\Config\Block;

use Drupal\tbo_emulate_bo\Plugin\Block\EmulateAdminCompanySessionBo;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'EmulateAdminCompanySessionBoBlockClass' block.
 */
class EmulateAdminCompanySessionBoBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_emulate_hn\Plugin\Block\EmulateAdminCompanySessionBo $instance
   * @param $config
   */
  public function setConfig(EmulateAdminCompanySessionBo &$instance, &$config) {
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
          'name' => ['title' => t('Empresa'), 'label' => 'Empresa', 'service_field' => 'company', 'show' => 1, 'weight' => 1, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => 200],
          'full_name' => ['title' => t('Nombre Admin empresa'), 'label' => 'Nombre Admin empresa', 'service_field' => 'admin_company', 'show' => 1, 'weight' => 2, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => 300],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'name' => ['title' => t('Empresa'), 'label' => 'Empresa', 'type' => 'company', 'service_field' => 'name', 'show' => 1, 'weight' => 1],
          'full_name' => ['title' => t('Nombre Admin empresa'), 'label' => 'Nombre Admin empresa', 'type' => 'user', 'service_field' => 'full_name', 'show' => 1, 'weight' => 2],
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
  public function build(EmulateAdminCompanySessionBo &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('config_name', 'emulateAdminCompanySessionBlock');
    $this->instance->setValue('directive', 'data-ng-emulate-session');
    $this->instance->setValue('class', 'wrapper-emulate block-emulate-admin-company-session');
    $filters_fields = $this->configuration['filters_fields'];
    // Set session var.
    $this->instance->cardBuildSession(TRUE, [], 'tbo_user');

    // Set title.
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    
 
    
    uasort($filters_fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));
   
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
    // Parameter additional.
    $others = [
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'emulate_session_bo',
      'library' => 'tbo_emulate_bo/emulate-session',      
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/emulate/bo/session?_format=json');

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'emulateAdminCompanySessionBlock');

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
