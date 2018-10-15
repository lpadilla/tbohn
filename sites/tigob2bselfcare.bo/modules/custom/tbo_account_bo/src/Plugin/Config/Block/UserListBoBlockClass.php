<?php

namespace Drupal\tbo_account_bo\Plugin\Config\Block;

use Drupal\tbo_account_bo\Plugin\Block\UsersListBoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

use Drupal\tbo_account\Plugin\Config\Block\UserListBlockClass;
use Drupal\tbo_account;

/**
 * Manage config a 'UserListBoBlockClass' block.
 */
class UserListBoBlockClass extends UserListBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_account_bo\Plugin\Block\UsersListBoBlock $instance
   * @param $config
   */
  public function setConfig(UsersListBoBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // Delete var session to validate create user.
    if (isset($_SESSION['render_user_list'])) {
      unset($_SESSION['render_user_list']);
      unset($_SESSION['render_user_list_title']);
    }

    return [
      'filters_options' => [
        'filters_fields' => [
          'full_name' => ['title' => t('Nombres'), 'label' => 'Nombres y/o Apellidos', 'service_field' => 'full_name', 'show' => 1, 'weight' => 1, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => '300'],
          'mail' => ['title' => t('Correo electrónico'), 'label' => 'ejemplo@tigo.com', 'service_field' => 'mail', 'show' => 1, 'weight' => 4, 'input_type' => 'email', 'class' => '3-columns', 'validate_length' => '200'],
          'group_name' => ['title' => t('Grupo'), 'label' => 'Grupo', 'service_field' => 'group_name', 'show' => 1, 'weight' => 7, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => '130'],
          'document_number' => ['title' => t('Número de documento'), 'label' => 'Número', 'service_field' => 'document_number', 'show' => 1, 'weight' => 3, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => '40'],
          'document_type' => ['title' => t('Documento'), 'label' => 'Documento', 'service_field' => 'document_type', 'show' => 1, 'weight' => 2, 'input_type' => 'text', 'type' => 'selectable', 'class' => '3-columns', 'none' => t('Ninguno')],
          'phone_number' => ['title' => t('Línea'), 'label' => 'Línea', 'service_field' => 'phone_number', 'show' => 1, 'weight' => 8, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => '20'],
          'user_role' => ['title' => t('Rol'), 'label' => 'Rol', 'service_field' => 'user_role', 'show' => 1, 'weight' => 5, 'input_type' => 'text', 'type' => 'selectable', 'class' => '3-columns', 'none' => t('Ninguno')],
          'company_name' => ['title' => t('Empresa'), 'label' => 'Nombre de la Empresa', 'service_field' => 'company_name', 'show' => 1, 'weight' => 6, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => '200'],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'full_name' => ['title' => t('Nombres'), 'label' => 'Nombres', 'type' => 'user', 'service_field' => 'full_name', 'show' => 1, 'weight' => 1],
          'mail' => ['title' => t('Correo electrónico'), 'label' => 'Correo electrónico', 'type' => 'user', 'service_field' => 'mail', 'show' => 1, 'weight' => 2],
          'group_name' => ['title' => t('Grupo'), 'label' => 'Grupo', 'type' => 'group', 'service_field' => 'group_name', 'show' => 1, 'weight' => 3],
          'document_number' => ['title' => t('Número de documento'), 'label' => 'Número de documento', 'type' => 'user', 'service_field' => 'document_number', 'show' => 1, 'weight' => 5],
          'document_type' => ['title' => t('Tipo de documento'), 'label' => 'Tipo de documento', 'type' => 'user', 'service_field' => 'document_type', 'show' => 1, 'weight' => 4],
          'phone_number' => ['title' => t('Línea'), 'label' => 'Línea', 'type' => 'user', 'service_field' => 'phone_number', 'show' => 1, 'weight' => 6],
          'user_role' => ['title' => t('Rol'), 'label' => 'Rol', 'type' => 'role', 'service_field' => 'user_role', 'show' => 1, 'weight' => 7],
          'company_name' => ['title' => t('Empresa'), 'label' => 'Empresa', 'type' => 'company', 'service_field' => 'company_name', 'show' => 1, 'weight' => 8],
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
    $form = $this->instance->cardBlockForm();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(UsersListBoBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'usersListBlock');
    $this->instance->setValue('directive', 'data-ng-users-list');
    $this->instance->setValue('class', 'wrapper-datausers block-users-list');

    // Ordering table_fields.
    $this->instance->ordering('filters_fields', 'filters_options');

    // Load documentType.
    $entities_service = \Drupal::service('tbo_entities.entities_service');
    $options = $entities_service->getDocumentTypes();
    $documentTypes = [];
    foreach ($options as $key => $value) {
      $documentTypes[$value['id']] = $value['label'];
    }

    $roles = user_role_names(TRUE);
    unset($roles['administrator']);
    unset($roles['super_admin']);
    unset($roles['tigo_admin']);
    unset($roles['authenticated']);
    unset($roles['admin_group']);

    // Set filters configurations.
    $filters = [];

    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $filters[$key_filter]['placeholder'] = isset($filter['label']) ? $filter['label']:'';
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        $filters[$key_filter]['type'] = isset($filter['type']) ? $filter['type'] : '';
        $filters[$key_filter]['input_type'] = $filter['input_type'];

        if (!empty($filter['validate_length'])) {
          $filters[$key_filter]['validate_length'] = $filter['validate_length'];
        }

        if ($key_filter == 'company_name') {
          // Admin two or more companies.
          $users_service = \Drupal::service('tbo_account.users');
          $companies = $users_service->getCompaniesByEntities(\Drupal::currentUser()->id());
          if (count($companies) < 2) {
            $company = reset($companies);
            $filters[$key_filter]['value'] = $company;
            $filter['disabled'] = 'disabled';
          }
        }
        $filters[$key_filter]['validations'] = $filter['validations'];
        if (isset($filter['disabled'])) {
          $filters[$key_filter]['disabled'] = $filter['disabled'];
        }
        if (isset($filter['none'])) {
          $filters[$key_filter]['none'] = $filter['none'];
        }
        if (isset($filter['type']) && ($filter['type'] == 'selectable_list' || $filter['type'] == 'selectable')) {
          if ($key_filter == 'document_type') {
            $filters[$key_filter]['options'] = $documentTypes;
          }
          if ($key_filter == 'user_role') {
            $filters[$key_filter]['options'] = $roles;
          }
        }
      }
    }

    // Set filters.
    $this->instance->setValue('filters', $filters);
     

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'users_list_bo',
      'library' => 'tbo_account_bo/users-list-bo',
    ];

    // Set title.
    $title = FALSE;
    if (!isset($_SESSION['render_user_create']) && $this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }
    
    $others = [
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Aditionals values to directive.
    $others = [
      'fields' => $this->instance->getValue('table_fields'),
    ];

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/api/usuarios?_format=json', $others);

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'usersListBlock');

    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account, $config) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();
    if (in_array('admin_company', $roles)) {
      // Set var session to validate create user.
      $_SESSION['render_user_list'] = TRUE;
      $_SESSION['render_user_list_title'] = $config['label'];

      return AccessResult::allowed();
    }
    

    return AccessResult::forbidden();

  }

}
