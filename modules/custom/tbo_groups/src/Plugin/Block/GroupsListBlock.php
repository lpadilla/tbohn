<?php

namespace Drupal\tbo_groups\Plugin\Block;

use Drupal\user\Entity\User;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Provides a 'GroupsListBlock' block.
 *
 * @Block(
 *  id = "groups_list_block",
 *  admin_label = @Translation("Listado de grupos"),
 * )
 */
class GroupsListBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_fields' => [
        'name' => [
          'title' => $this->t("Grupo"),
          'service_field' => 'name',
          'show' => 1,
          'weight' => 1,
          'class' => '1-columns',
          'max_length' => 145,
        ],

        'administrator' => [
          'title' => $this->t("Administrador"),
          'service_field' => 'administrator',
          'show' => 1,
          'weight' => 2,
          'class' => '1-columns',
          'max_length' => 200,
        ],

      ],
      'table_fields' => [
        'name' => [
          'title' => $this->t("Grupo"),
          'service_field' => 'name',
          'show' => 1,
          'weight' => 1,
          'class' => 'double-top-and-bottom-padding',
        ],

        'administrator' => [
          'title' => $this->t("Administrador"),
          'service_field' => 'administrator',
          'show' => 1,
          'weight' => 2,
          'class' => 'double-top-and-bottom-padding',
        ],

        'associated_accounts' => [
          'title' => $this->t("Cuentas asociadas"),
          'service_field' => 'associated_accounts',
          'show' => 1,
          'weight' => 3,
          'class' => 'double-top-and-bottom-padding',
        ],

        'lines' => [
          'title' => $this->t("Líneas"),
          'service_field' => 'lines',
          'show' => 1,
          'weight' => 3,
          'class' => 'double-top-and-bottom-padding',
        ],

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

    // Crear el fieldset que contiene los filtros de la tabla.
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

    // filters_fields: variable que contendra la configuracion por defecto de los filtros.
    $filters_fields = $this->configuration['filters_fields'];

    // Se ordenan los filtros segun lo establecido en la configuración.
    uasort($filters_fields, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    // Se crean todos los campos de filtro que contendra el bloque.
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
        ],
        '#default_value' => $entity['class'],
      ];

      $form['filters_options']['filters_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    // table_options: fieldset que contiene todas las columnas de la tabla.
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

    // $table_fields: variable que contiene la configuracion por defecto de las columnas de la tabla.
    $table_fields = $this->configuration['table_fields'];

    // Se ordenan los filtros segun lo establecido en la configuración.
    uasort($table_fields, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    // Se crean todas las columnas de la tabla que mostrara la información.
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
    $this->configuration['table_fields'] = $form_state->getValue([
      'table_options',
      'table_fields',
    ]);
    $this->configuration['filters_fields'] = $form_state->getValue([
      'filters_options',
      'filters_fields',
    ]);
    $this->configuration['others'] = $form_state->getValue([
      'others',
      'config',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /**
     * Variables dentro del build del bloque
     *
     * $uuid => el id unico del display del bloque
     * $this_display => Numero aleatorio que permite saber la configuracion exacta del bloque que se esta mostrando "esto por si se coloca el mismo bloque mas de una vez en el mismo page."
     * $filters_fields => Contiene la configuracion de los filtros del bloque
     * $table_fields => Contiene la configuracion de las columnas a mostrar en la tabla
     * $filters => Array que contiene solamente los filtros activos en la configuracion
     * $columns => Array que contiene solamente los campos activos en la configuracion
     * $headers_table => Array que contiene los campos y el nombre de la tabla a buscar en el query del recurso rest
     * $group_fields => Array que contiene los campos de la compañia para relacionarlos con las columnas activas
     * $tempstore => Se obtiene el servicio user.private_tempstore para almacenar valores temporales.
     * $form => Contiene la instancia del formulario CreateEnterpriseForm
     *
     */

    $uuid = $this->configuration['uuid'];
    $this_display = rand();
    $filters_fields = $this->configuration['filters_fields'];
    $table_fields = $this->configuration['table_fields'];

    uasort($filters_fields, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    $filters = [];
    foreach ($filters_fields as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        if ($key_filter == 'name') {
          $filters[$key_filter]['autocomplete'] = TRUE;
        }
        if ($filter['max_length'] != 0 || $filter['max_length'] != '') {
          $filters[$key_filter]['validate_length'] = $filter['max_length'];
        }
      }
    }

    uasort($table_fields, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    $columns = [];
    $headers_table = [];
    $group_fields = [
      'name' => 'name',
      'administrator' => 'administrator',
      'associated_accounts' => 'associated_accounts',
    ];

    // Recorremos $table_fields para verificar las columnas a mostrar.
    foreach ($table_fields as $key_field => $field) {
      if ($field['show'] == 1) {
        $columns[$key_field]['key'] = $key_field;
        $columns[$key_field]['label'] = $field['title'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $columns[$key_field]['class'] = implode(" ", $classes);
        $columns[$key_field]['service_field'] = $field['service_field'];
        if (array_key_exists($key_field, $group_fields)) {
          $headers_table[$key_field]['value'] = $key_field;
          $headers_table[$key_field]['type'] = 'group';
        }
        else {
          if ($key_field == 'user_name') {
            $headers_table[$key_field]['value'] = 'name';
          }
          else {
            $headers_table[$key_field]['value'] = $key_field;
          }
          $headers_table[$key_field]['type'] = 'user';
        }
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Se obtiene el servicio drupal para almacenar valores temporales y se crear la variable que contiene las columnas a llamar en el query sql.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_groups');
    $tempstore->set('block_groups_list_columns_' . $this_display, $headers_table);

    // Se obtiene el formulario para la de empresa.
    $form = \Drupal::formBuilder()
      ->getForm('\Drupal\tbo_groups\Form\CreateGroupForm');

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $build = [
      '#theme' => 'groups_list',
      '#uuid' => $uuid,
      '#config' => $this->configuration,
      '#fields' => $columns,
      '#form' => $form,
      '#filters' => $filters,
      '#attached' => [
        'library' => [
          'tbo_groups/groups-list',
        ],
      ],
      '#plugin_id' => $this->getPluginId(),
    ];

    // Se obtiene la configuracion del paginador.
    $config_pager['page_elements'] = $this->configuration['others']['paginate']['number_rows_pages'];
    $tempstore->set('block_groups_list_columns_pager' . $this_display, $this->configuration['others']['paginate']);
    // Se carga los datos necesarios para la directiva angular.
    $config_block = [
      'url' => '/tboapi/groups/list?_format=json',
      'uuid' => $uuid,
      'filters' => $filters,
      'config_pager' => $config_pager,
      'config_columns' => $this_display,
    ];

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $build['#attached']['drupalSettings']['groupsListBlock'][$uuid] = $config_block;

    // Se guarda el log de auditoria.
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

    $log->set('user_names', $name);
    $log->set('created', time());
    $log->set('user_id', $uid);
    $log->set('user_names', $name);
    $log->set('user_role', $account->get('roles')->getValue()[0]['target_id']);
    $log->set('event_type', 'Cuenta');
    $log->set('description', 'Usuario accede a activacion de empresas');
    $log->set('details', 'Usuario ' . $name . ' consulta activación de empresas');
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
