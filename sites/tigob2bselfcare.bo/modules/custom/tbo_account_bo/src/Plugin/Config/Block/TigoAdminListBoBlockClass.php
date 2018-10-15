<?php

namespace Drupal\tbo_account_bo\Plugin\Config\Block;

use Drupal\tbo_account_bo\Plugin\Block\TigoAdminListBoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'TigoAdminListBoBlockClass' block.
 */
class TigoAdminListBoBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param TigoAdminListBlock $instance
   * @param $config
   */
  public function setConfig(TigoAdminListBoBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'filters_options' => [
        'filters_fields' => [
          'full_name' => ['title' => t('Nombre'), 'placeholder' => 'Nombre', 'label' => 'Nombre', 'service_field' => 'full_name', 'show' => 1, 'weight' => 1, 'class' => '1-columns'],
          'mail' => ['title' => t('Correo electrónico'), 'placeholder' => 'Correo electrónico', 'label' => 'Correo electrónico', 'service_field' => 'mail', 'show' => 1, 'weight' => 2, 'class' => '1-columns'],
          'status' => ['title' => t('Estado'), 'label' => 'Estado', 'service_field' => 'status', 'show' => 1, 'weight' => 3, 'class' => '1-columns'],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'full_name' => ['title' => t('Nombre'), 'label' => t('Nombre'), 'type' => 'user', 'service_field' => 'full_name', 'show' => 1, 'weight' => 1,],
          'mail' => ['title' => t('Correo electrónico'), 'label' => t('Correo electrónico'), 'type' => 'user', 'service_field' => 'mail', 'show' => 1, 'weight' => 2,],
          'companies' => ['title' => t('Empresas'), 'label' => t('Empresas'), 'type' => 'company', 'service_field' => 'companies', 'show' => 1, 'weight' => 3,],
          'status' => ['title' => t('Estado'), 'label' => t('Estado'), 'type' => 'user', 'service_field' => 'status', 'show' => 1, 'weight' => 4,],
          'assign_enterprise' => ['title' => t('Asignar empresa'), 'label' => t('Asignar empresa'), 'type' => 'company', 'service_field' => 'assign_enterprise', 'show' => 1, 'weight' => 6,],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [
            'number_pages' => 30,
            'number_rows_pages' => 5,
          ],
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'url_config' => 'reasignar-empresas'
        ],
      ],
      'not_show_class' => [
        'columns' => 1
      ],
    );
  }

	  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $field['url_config'] = array(
      '#type' => 'textfield',
      '#title' => t('Url reasignar empresas'),
      '#description' => 'Ingrese la Url para reasignar empresa, por ejemplo reasignar-empresas',
      '#default_value' => $this->configuration['others']['config']['url_config'],
      '#required' => TRUE,
      '#size' => 64,
    );

    $form = $this->instance->cardBlockForm($field);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'TigoAdminListBlock');
    $this->instance->setValue('directive', 'data-ng-manage-users-tigo-admin');
    $this->instance->setValue('class', 'user-tigotbo manage-users-tigo-admin');
    $this->instance->ordering('filters_fields', 'filters_options');

    //Set filters configurations
    $filters = array();
    foreach ($this->instance->getValue('filters_fields') as $key => $value){
      if($value['show'] == 1){
        $filters[$key]['identifier'] = $key;
        $filters[$key]['label'] = $value['title'];
        $filters[$key]['placeholder'] = isset($value['label']) ? $value['label']:'';
        $filters[$key]['service_field'] = $value['service_field'];
        $class = array('field-'.$value['service_field'], $value['class']);
        $filters[$key]['class'] = implode(" ", $class);

        if($value['service_field'] == 'full_name'){
          $filters[$key]['validate_length'] = 300;
        } else if($value['service_field'] == 'mail'){
          $filters[$key]['validate_length'] = 200;
        }

        if($key == 'status'){
          $filters[$key]['type'] = 'select';
          $filters[$key]['none'] = 'Seleccionar';
          $filters[$key]['options'] = array(1 =>'Activo', 0 => 'Inactivo');
        }

        if($key == 'mail'){
          $filters[$key]['input_type'] = 'email';
        }
      }
    }

    //Set filters
    $this->instance->setValue('filters', $filters);

    //Ordering table_fields
    $this->instance->ordering('table_fields', 'table_options');

    $data = array();
    foreach($this->instance->getValue('table_fields') as $key => $value){
      if($value['show'] == 1){
        $data[$key]['identifier'] = $key;
        $data[$key]['label'] = $value['label'];
        $data[$key]['service_field'] = $value['service_field'];
        $data[$key]['type'] = $value['type'];
        $class = array('field-'.$value['service_field'], $value['class']);
        $data[$key]['class'] = implode(" ", $class);
      }
    }

    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account_bo\Form\CreateUsersBoForm');

    //set title
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    $build = array(
      '#theme' => 'tigo_admin_list_bo',
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
      '#attached' => array(
        'library' => array(
          'tbo_account_bo/tigoAd-list-bo',     
          'tbo_account/tigoAd-list'
        ),
      ),
    );

    //Set columns and headers_table_query
    $this->instance->setValue('build', $build);

    //Set data to directive
    $other_config = [
      'fields' => $data,
    ];

    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock('/api/tigo-admin-list', $other_config);

    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'TigoAdminListBlock');  //OJO en esta funcion seguimos usando librerias y elemento de enlace para angular que son de TigoAdminListBlock

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
