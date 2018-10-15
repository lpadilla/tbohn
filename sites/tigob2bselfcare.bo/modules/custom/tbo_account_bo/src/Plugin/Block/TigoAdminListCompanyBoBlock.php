<?php

namespace Drupal\tbo_account_bo\Plugin\Block;

use Drupal\tbo_account\Plugin\Block\TigoAdminListCompanyBlock;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'TigoAdminListCompanyBoBlock' block.
 *
 * @Block(
 *  id = "tigo_admin_list_company_bo_block",
 *  admin_label = @Translation("Listado Empresas Tigo Admin BO"),
 * )
 */
class TigoAdminListCompanyBoBlock extends TigoAdminListCompanyBlock {

	

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $uuid = $this->configuration['uuid'];
    $this_display = rand();
    $filters_fields = $this->configuration['filters_fields'];
    $table_fields = $this->configuration['table_fields'];

    uasort($filters_fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    //load filters
    $filters = [];
    foreach ($filters_fields as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $filters[$key_filter]['placeholder'] = isset($filter['label']) ? $filter['label']:'';
        $classes = ["field-" . $filter['service_field'], $filter['class'], $filter['padding']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        if ($filter['max_length'] != 0 || $filter['max_flength'] != '') {
          $filters[$key_filter]['validate_length'] = $filter['max_length'];
        }
        if ($filter['service_field'] == 'doc_type') {
          $filters[$key_filter]['type'] = 'select';
          //Se obtienen los tipos de documento de la base de datos
          $documents = \Drupal::service('tbo_entities.entities_service');
          $options_service = $documents->getDocumentTypes();

          $options = [];
          foreach ($options_service as $key => $data) {
            $options[$data['id']] = $data['label'];
          }

          $filters[$key_filter]['options'] = $options;
          $filters[$key_filter]['none'] = $this->t('Seleccione opción');
        }
        if ($filter['service_field'] == 'status' || $filter['service_field'] == 'status_tigo_admin') {
          $filters[$key_filter]['type'] = 'select';
          $options = [1 => 'Activo', 0 => 'Inactivo'];
          $filters[$key_filter]['options'] = $options;
          $filters[$key_filter]['none'] = $this->t('Seleccione opción');
        }
      }
    }

    uasort($table_fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    //load config fields table and build fields
    $table_fields = $this->configuration['table_fields'];
    uasort($table_fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));
    $headers_table = [];
    $user_fields = [
      'name' => 'name',
      'full_name' => 'full_name',
      'status_tigo_admin' => 'status',
    ];

    foreach ($table_fields as $key_field => $field){
      if($field['show'] == 1){
        $headers_table[$key_field]['identifier'] = $key_field;
        $headers_table[$key_field]['label'] = $field['title'];
        $classes = [ "field-".$field['service_field'], $field['class']];
        $headers_table[$key_field]['class'] = implode(" ", $classes);
        $headers_table[$key_field]['service_field'] = $field['service_field'];
        if(array_key_exists($key_field, $user_fields)) {
          $headers_table[$key_field]['value'] = $user_fields[$key_field];
          $headers_table[$key_field]['type'] = 'user';
        }
        else {
          $headers_table[$key_field]['value'] = $key_field;
          $headers_table[$key_field]['type'] = 'company';
        }
        unset($classes);
      } else {
        unset($field[$key_field]);
      }
    }

    //load limit
    $paginate = $this->configuration['others']['paginate'];
    $limit = $paginate['number_pages'] * $paginate['number_rows_pages'];

    //save columns in filters
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
    $tempstore->set('tigo_admin_list_company_block' . $this_display, $headers_table);
    $tempstore->set('tigo_admin_list_company_block_limit' . $this_display, $limit);

    //get paginate to config card
    $config_pager['pages'] = $paginate['number_pages'];
    $config_pager['page_elements'] = $paginate['number_rows_pages'];

    //Build build with template variables
    $build = array(
      '#theme' => 'tigo_admin_list_companies_bo',
      '#uuid' => $uuid,
      '#filters' => $filters,
      '#headers_table' => $headers_table,
      '#attached' => array(
        'library' => array(
          'tbo_account_bo/tigo-admin-list-companies-bo',
        ),
      ),
    );

    //variable to send settings to angular
    $config_block = array(
      'url' => '/tboapi/account/tigo-admin-list-company?_format=json',
      'uuid' => $uuid,
      'filters' => $filters,
      'config_pager' => $config_pager,
      'display' => $this_display,
    );

    //send config to angular
    $build['#attached']['drupalSettings']['companiesManageBlock'][$uuid] = $config_block;

    //Save auditory log
    $log = AuditLogEntity::create();
    $uid = \Drupal::currentUser()->id();
    $account = \Drupal\user\Entity\User::load($uid);
    //Load fields account
    $account_fields = \Drupal::currentUser()->getAccount();
    if(isset($account_fields->full_name) && !empty($account_fields->full_name)){
      $name = $account_fields->full_name;
    }else{
      $name = \Drupal::currentUser()->getAccountName();
    }

    //get name rol
    $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);

    $log->set('created', time());
    $log->set('company_name', '');
    $log->set('company_document_number', '');
    $log->set('user_id', $uid);
    $log->set('user_names', $name);
    $log->set('user_role', $rol);
    $log->set('event_type', 'TigoAdmin');
    $log->set('description', 'Consulta listado de empresas tigo admin');
    $log->set('details', 'Usuario ' . $name . ' consultó el listado de empresas por usuario tigo admin');
    $log->save();

    return $build;
  }

}