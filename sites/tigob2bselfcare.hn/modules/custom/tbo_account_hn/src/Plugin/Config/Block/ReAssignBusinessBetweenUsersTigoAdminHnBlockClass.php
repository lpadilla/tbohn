<?php

namespace Drupal\tbo_account_hn\Plugin\Config\Block;

use Drupal\tbo_account_hn\Plugin\Block\ReAssignBusinessBetweenUsersTigoAdminHnBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

use Drupal\tbo_account\Plugin\Config\Block\ReAssignBusinessBetweenUsersTigoAdminBlockClass;
use Drupal\tbo_account;

/**
 * Manage config a 'ReAssignBusinessBetweenUsersTigoAdminHnBlockClass' block.
 */
class ReAssignBusinessBetweenUsersTigoAdminHnBlockClass extends ReAssignBusinessBetweenUsersTigoAdminBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param ReAssignBusinessBetweenUsersTigoAdminHnBlock $instance
   * @param $config
   */
  public function setConfig(ReAssignBusinessBetweenUsersTigoAdminHnBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }


  /**
   * {@inheritdoc}
   */
  public function build(ReAssignBusinessBetweenUsersTigoAdminHnBlock &$instance, &$config) {
    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'ReAssingBusinessBetweenUsersTigoAdminBlock');
    $this->instance->setValue('directive', 'data-ng-re-assign-between-users-tigo-admin');
    $this->instance->setValue('class', 'wrapper-toassign block-re-assign-between-users-tigo-admin');

    //Ordering table_fields
    $this->instance->ordering('filters_fields', 'filters_options');

    //Set filters configurations
    $filters = array();
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
       // $filters[$key_filter]['label'] = $filter['title'];
        $filters[$key_filter]['placeholder'] = isset($filter['label']) ? $filter['label']:'';
        $classes = [ "field-".$filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];

        $filters[$key_filter]['type'] = isset($filter['type'])?$filter['type']:'';
        $filters[$key_filter]['input_type'] = $filter['input_type'];

        if($key_filter == 'name') {
          $filters[$key_filter]['autocomplete'] = TRUE;
        }
        if($filter['max_length'] != 0 || $filter['max_length'] != '') {
          $filters[$key_filter]['validate_length'] = $filter['max_length'];
        }
      }
    }

    //Set filters
    $this->instance->setValue('filters', $filters);

    //Ordering table_fields
    $this->instance->ordering('table_fields', 'table_options');

    $data = $headers_table = [];
    foreach ($this->instance->getValue('table_fields') as $key_field => $field)
    {
      if ($field['label'] == 'Reasignar')
      {
          $field['show'] = 1;
      }
      if ($field['show'] == 1)
      {
        $data[$key_field]['key'] = $key_field;
        $data[$key_field]['label'] = $field['label'];
        $classes = [ "field-".$field['service_field'], $field['class']];
        $data[$key_field]['class'] = implode(" ", $classes);
        $data[$key_field]['service_field'] = $field['service_field'];

        if (isset($field['input_type']))
        {
          $data[$key_field]['identifier'] = $key_field;
          $data[$key_field]['input_type'] = $field['input_type'];
        }
        $data[$key_field]['type'] = isset($field['type'])?$field['type']:'';

        $headers_table[$key_field]['value'] = $key_field;

        if ($key_field == 'name')
        {
          $headers_table[$key_field]['type'] = 'company';
        }
        else
        {
          $headers_table[$key_field]['type'] = 'user';
          if ($headers_table[$key_field]['value'] == 'user_name')
          {
            $headers_table[$key_field]['value'] = 'full_name';
          }
        }
        $headers_table[$key_field]['label'] = $data[$key_field]['label'];
        $headers_table[$key_field]['class'] = $data[$key_field]['class'];
        unset($classes);
      }
    }

    //Set columns and headers_table_query
    $this->instance->setColumns($data, $headers_table);

    //Set session var
    $this->instance->cardBuildSession();

    //set title
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    //Get id tigo_admin
    $current_path = \Drupal::service('path.current')->getPath();
    $parts = explode('/',$current_path);
    $tigoadmin = $parts[count($parts)-1];

    $servicio = \Drupal::service('tbo_account.companies_list_tigoadmin');

    $nombreTigo = $servicio->getFullName($tigoadmin);
    $listaTigos = $servicio->getTigoAdmins($tigoadmin);

    $usrtigo = [];
    $usrtigo['uid'] = $tigoadmin;
    $usrtigo['nombre'] = $nombreTigo['full_name'];

    $build = [
      '#theme' => 're_assing_business_between_users_tigo_admin_hn',
      '#uuid' => $this->instance->getValue('uuid'),
      '#config' => $this->configuration,
      '#fields' => $data,
      '#usrtigo' => $usrtigo,
      '#lsttigos' => $listaTigos,
      '#filters' => $filters,
      '#directive' => $this->instance->getValue('directive'),
      '#class' => $this->instance->getValue('class'),
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#attached' => array(
        'library' =>  array(
          'tbo_account_hn/re-assing-business-between-users-tigo-admin-hn'
        ),
      ),
    ];

    //Set columns and headers_table_query
    $this->instance->setValue('build', $build);

    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/account/list/'.$tigoadmin.'?_format=json');

    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'ReAssingBusinessBetweenUsersTigoAdminBlock');

    //Save Audit log
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $user_names = $service_log->getName();

    //Create array data[]
    $data = [
      'event_type' => 'Cuenta',
      'description' => 'Usuario consulta listado de empresas asignadas a usuarios TigoAdmin',
      'details' => 'Usuario '. $user_names. ' consultó listado de empresas asociadas a usuarios Tigo Admin',
    ];

    //Save audit log
    $service_log->insertGenericLog($data);

    return $this->instance->getValue('build');
  }

}
