<?php

namespace Drupal\tbo_account\Plugin\Block;

use Drupal\user\Entity\User;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Provides a 'TigoAdminListCompanyBlock' block.
 *
 * @Block(
 *  id = "tigo_admin_list_company_block",
 *  admin_label = @Translation("Listado Empresas Tigo Admin"),
 * )
 */
class TigoAdminListCompanyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_fields' => [
        'name' => ['title' => $this->t("Usuario"), 'service_field' => 'name', 'show' => 1, 'weight' => 1, 'class' => '1-columns', 'max_length' => 200],
        'full_name' => ['title' => $this->t("Nombre completo"), 'service_field' => 'full_name', 'show' => 1, 'weight' => 2, 'class' => '1-columns', 'max_length' => 200],
        'status_tigo_admin' => ['title' => $this->t("Estado Usuario"), 'service_field' => 'status_tigo_admin', 'show' => 1, 'weight' => 3, 'class' => '1-columns'],
        'company_name' => ['title' => $this->t("Empresa"), 'service_field' => 'company_name', 'show' => 1, 'weight' => 4, 'class' => '1-columns', 'max_length' => 200],
        'document_type' => ['title' => $this->t("Tipo de documento"), 'service_field' => 'doc_type', 'show' => 1, 'weight' => 5, 'class' => '1-columns'],
        'document_number' => ['title' => $this->t("Documento empresa"), 'service_field' => 'document_number', 'show' => 1, 'weight' => 6, 'class' => '1-columns', 'max_length' => 200],
        'status' => ['title' => $this->t("Estado Company"), 'service_field' => 'status', 'show' => 1, 'weight' => 7, 'class' => '1-columns'],
      ],
      'table_fields' => [
        'name' => ['title' => $this->t("Usuario"), 'service_field' => 'name', 'show' => 1, 'weight' => 1, 'class' => '1-columns'],
        'full_name' => ['title' => $this->t("Nombre completo"), 'service_field' => 'full_name', 'show' => 1, 'weight' => 3, 'class' => '1-columns'],
        'status_tigo_admin' => ['title' => $this->t("Estado Usuario"), 'service_field' => 'status_tigo_admin', 'show' => 1, 'weight' => 3, 'class' => '1-columns'],
        'company_name' => ['title' => $this->t("Empresa"), 'service_field' => 'company_name', 'show' => 1, 'weight' => 4, 'class' => '1-columns'],
        'document_type' => ['title' => $this->t("Tipo de documento"), 'service_field' => 'doc_type', 'show' => 1, 'weight' => 4, 'class' => '1-columns'],
        'document_number' => ['title' => $this->t("Documento empresa"), 'service_field' => 'company_name', 'show' => 1, 'weight' => 4, 'class' => '1-columns'],
        'status' => ['title' => $this->t("Estado Company"), 'service_field' => 'status', 'show' => 1, 'weight' => 4, 'class' => '1-columns'],
      ],
      'others' => [
        'paginate' => [
          'number_pages' => 10,
          'number_rows_pages' => 10,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // Filtros tabla.
    $form['filters_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Opciones de los filtros'),
      '#open' => TRUE,
    ];
    $form['filters_options']['filters_fields'] = [
      '#type' => 'table',
      '#header' => [t('Field'), t('Show'), t('Weight'), t('Espaciado'), ''],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];

    $filters_fields = $this->configuration['filters_fields'];
    uasort($filters_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($filters_fields as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['filters_options']['filters_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['filters_options']['filters_fields']['#weight'] = $entity['weight'];

      // Some table columns containing raw markup.
      $form['filters_options']['filters_fields'][$id]['label'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['filters_options']['filters_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // TableDrag: Weight column element.
      $form['filters_options']['filters_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      $form['filters_options']['filters_fields'][$id]['class'] = [
        '#type' => 'select',
        '#options' => [
          '' => $this->t('Ninguno'),
          'destacado' => $this->t('Destacado'),
          '1-columns' => $this->t('Una columna'),
          '2-columns' => $this->t('Dos columnas'),
          '3-columns' => $this->t('Tres columnas'),
          '4-columns' => $this->t('Cuatro columnas'),
          '5-columns' => $this->t('Cinco columnas'),
          '6-columns' => $this->t('Seis columnas'),
          '7-columns' => $this->t('Siete columnas'),
          '8-columns' => $this->t('Ocho columnas'),
          '9-columns' => $this->t('Nueve columnas'),
          '10-columns' => $this->t('Diez columnas'),
          '11-columns' => $this->t('Once columnas'),
          '12-columns' => $this->t('Doce columnas'),
        ],
        '#default_value' => $entity['class'],
      ];

      $form['filters_options']['filters_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    // Vista de la tabla.
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones tabla'),
      '#open' => TRUE,
    ];
    $form['table_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Field'), t('Show'), t('Weight'), t('Espaciado'), ''],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];

    $table_fields = $this->configuration['table_fields'];
    uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($table_fields as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table_options']['table_fields']['#weight'] = $entity['weight'];

      // Some table columns containing raw markup.
      $form['table_options']['table_fields'][$id]['label'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['table_options']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // TableDrag: Weight column element.
      $form['table_options']['table_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      $form['table_options']['table_fields'][$id]['class'] = [
        '#type' => 'select',
        '#options' => [
          '' => $this->t('Ninguno'),
          'destacado' => $this->t('Destacado'),
          '1-columns' => $this->t('Una columna'),
          '2-columns' => $this->t('Dos columnas'),
          '3-columns' => $this->t('Tres columnas'),
          '4-columns' => $this->t('Cuatro columnas'),
        ],
        '#default_value' => $entity['class'],
      ];

      $form['table_options']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    $form['others'] = [
      '#type' => 'details',
      '#title' => $this->t('Otras configuraciones'),
      '#open' => TRUE,
    ];

    $form['others']['config']['paginate'] = [
      '#type' => 'details',
      '#title' => $this->t('Configurar Paginador'),
      '#open' => TRUE,
    ];

    $form['others']['config']['paginate']['number_pages'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Numero de Paginas a mostrar'),
      '#default_value' => $this->configuration['others']['paginate']['number_pages'],
    ];

    $form['others']['config']['paginate']['number_rows_pages'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Numero de elementos a mostrar por pagina'),
      '#default_value' => $this->configuration['others']['paginate']['number_rows_pages'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['table_fields'] = $form_state->getValue(['table_options', 'table_fields']);
    $this->configuration['filters_fields'] = $form_state->getValue(['filters_options', 'filters_fields']);
    $this->configuration['others'] = $form_state->getValue(['others', 'config']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $uuid = $this->configuration['uuid'];
    $this_display = rand();
    $filters_fields = $this->configuration['filters_fields'];
    $table_fields = $this->configuration['table_fields'];

    uasort($filters_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    // Load filters.
    $filters = [];
    foreach ($filters_fields as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $classes = ["field-" . $filter['service_field'], $filter['class'], $filter['padding']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        if ($filter['max_length'] != 0 || $filter['max_length'] != '') {
          $filters[$key_filter]['validate_length'] = $filter['max_length'];
        }
        if ($filter['service_field'] == 'doc_type') {
          $filters[$key_filter]['type'] = 'select';
          // Se obtienen los tipos de documento de la base de datos.
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

    uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    // Load config fields table and build fields.
    $table_fields = $this->configuration['table_fields'];
    uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $headers_table = [];
    $user_fields = [
      'name' => 'name',
      'full_name' => 'full_name',
      'status_tigo_admin' => 'status',
    ];

    foreach ($table_fields as $key_field => $field) {
      if ($field['show'] == 1) {
        $headers_table[$key_field]['identifier'] = $key_field;
        $headers_table[$key_field]['label'] = $field['title'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $headers_table[$key_field]['class'] = implode(" ", $classes);
        $headers_table[$key_field]['service_field'] = $field['service_field'];
        if (array_key_exists($key_field, $user_fields)) {
          $headers_table[$key_field]['value'] = $user_fields[$key_field];
          $headers_table[$key_field]['type'] = 'user';
        }
        else {
          $headers_table[$key_field]['value'] = $key_field;
          $headers_table[$key_field]['type'] = 'company';
        }
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Load limit.
    $paginate = $this->configuration['others']['paginate'];
    $limit = $paginate['number_pages'] * $paginate['number_rows_pages'];

    // Save columns in filters.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
    $tempstore->set('tigo_admin_list_company_block' . $this_display, $headers_table);
    $tempstore->set('tigo_admin_list_company_block_limit' . $this_display, $limit);

    // Get paginate to config card.
    $config_pager['pages'] = $paginate['number_pages'];
    $config_pager['page_elements'] = $paginate['number_rows_pages'];

    // Build build with template variables.
    $build = [
      '#theme' => 'tigo_admin_list_companies',
      '#uuid' => $uuid,
      '#filters' => $filters,
      '#headers_table' => $headers_table,
      '#attached' => [
        'library' => [
          'tbo_account/tigo-admin-list-companies',
        ],
      ],
      '#plugin_id' => $this->getPluginId(),
    ];

    // Variable to send settings to angular.
    $config_block = [
      'url' => '/tboapi/account/tigo-admin-list-company?_format=json',
      'uuid' => $uuid,
      'filters' => $filters,
      'config_pager' => $config_pager,
      'display' => $this_display,
    ];

    // Send config to angular.
    $build['#attached']['drupalSettings']['companiesManageBlock'][$uuid] = $config_block;

    // Save auditory log.
    $log = AuditLogEntity::create();
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    // Load fields account.
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }

    // Get name rol.
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

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
