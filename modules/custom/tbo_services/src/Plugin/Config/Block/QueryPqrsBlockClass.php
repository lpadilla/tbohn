<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\adf_core\Util\UtilElement;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\tbo_services\Plugin\Block\QueryPqrsBlock;

/**
 * Manage config a 'SearchByProfileBlockClass' block.
 */
class QueryPqrsBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Set instance configuration block.
   *
   * @param \Drupal\tbo_services\Plugin\Block\QueryPqrsBlock $instance
   *   Instance SearchByProfileBlock block.
   * @param array $config
   *   Instance config block.
   */
  public function setConfig(QueryPqrsBlock &$instance, array &$config) {
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
          'exact_search' => [
            'title' => 'Busquedad Exacta',
            'label' => 'Busquedad Exacta',
            'service_field' => 'exact_search',
            'show' => 1,
            'weight' => 1,
            'class' => '3-columns',
          ],
          'user' => [
            'title' => 'Usuario',
            'label' => 'Usuario',
            'service_field' => 'user',
            'show' => 1,
            'weight' => 1,
            'class' => '3-columns',
          ],
          'request_code' => [
            'title' => 'Código de solicitud',
            'label' => 'Código de solicitud',
            'service_field' => 'request_code',
            'show' => 1,
            'weight' => 2,
            'class' => '3-columns',
          ],
          'status' => [
            'title' => 'Estado',
            'label' => 'Estado',
            'service_field' => 'status',
            'show' => 1,
            'weight' => 3,
            'class' => '3-columns',
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'card_pqrs' => [
            'title' => 'Card Pqrs',
            'service_field' => 'card_pqrs',
            'show' => 1,
            'weight' => 1,
          ],
        ],
        'left' => [
          'table_fields' => [
            'type' => [
              'title' => 'Tipo',
              'label' => 'Tipo',
              'service_field' => 'type',
              'position' => 'left',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 1,
            ],
            'request_code' => [
              'title' => 'Código de solicitud',
              'label' => 'Código de solicitud',
              'service_field' => 'request_code',
              'position' => 'left',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 2,
            ],

          ],
        ],
        'center' => [
          'table_fields' => [
            'user' => [
              'title' => 'Usuario',
              'label' => 'Usuario',
              'service_field' => 'user',
              'position' => 'center',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 1,
            ],
            'email' => [
              'title' => t("Email"),
              'service_field' => 'email',
              'position' => 'center',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 2,
            ],

          ],
        ],
        'right' => [
          'table_fields' => [
            'status' => [
              'title' => 'Estado',
              'label' => 'Estado',
              'service_field' => 'status',
              'show' => 1,
              'weight' => 1,
              'position' => 'right',
              'class' => '6-columns',
            ],
          ],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'card_pqrs' => [
            'title' => 'Card Details Pqrs',
            'service_field' => 'card_pqrs',
            'show' => 1,
            'weight' => 1,
          ],
        ],
        'left' => [
          'table_fields' => [
            'serviceCollection_information' => [
              'title' => t("Mostrar datos alineados a la izquierda"),
              'label' => 'Información',
              'service_field' => 'serviceCollection_information',
              'weight' => 1,
              'show' => 1,
            ],
            'filing_date' => [
              'title' => 'Fecha radicado',
              'label' => 'Fecha radicado',
              'service_field' => 'filing_date',
              'show' => 1,
              'weight' => 2,
              'position' => 'left',
              'class' => '12-columns',
            ],
            'due_date' => [
              'title' => 'Fecha vencimiento',
              'label' => 'Fecha vencimiento',
              'service_field' => 'due_date',
              'show' => 1,
              'weight' => 3,
              'position' => 'left',
              'class' => '12-columns',
            ],
            'filing_number' => [
              'title' => 'Numero de radicado',
              'label' => 'Numero de radicado',
              'service_field' => 'filing_number',
              'show' => 1,
              'weight' => 4,
              'position' => 'left',
              'class' => '12-columns',
            ],
            'sic_link' => [
              'title' => 'Link SIC',
              'label' => 'Link SIC',
              'service_field' => 'sic_link',
              'show' => 1,
              'weight' => 5,
              'position' => 'left',
              'class' => '12-columns',
            ],
            'product_line' => [
              'title' => 'Producto/Línea',
              'label' => 'Producto/Línea',
              'service_field' => 'product_line',
              'show' => 1,
              'weight' => 6,
              'position' => 'left',
              'class' => '12-columns',
            ],
          ],
        ],
        'right' => [
          'table_fields' => [
            'serviceCollection_status' => [
              'title' => t("Mostrar datos alineados a la derecha"),
              'service_field' => 'serviceCollection_status',
              'show' => 1,
            ],
            'state_case' => [
              'title' => 'Estado del caso',
              'label' => 'Estado del caso',
              'service_field' => 'state_case',
              'show' => 1,
              'weight' => 2,
              'class' => '12-columns',
            ],
            'link_definitions' => [
              'title' => 'Link definiciones',
              'service_field' => 'link_definitions',
              'show' => 1,
              'weight' => 3,
              'class' => '12-columns',
            ],
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'detail' => [
            'title' => t('Botón ver detalles'),
            'label' => 'DETALLES',
            'service_field' => 'detail',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
          'rdb_query' => [
            'title' => t('Radio Button Consultar'),
            'label' => 'Consultar',
            'service_field' => 'rdb_query',
            'show' => 1,
            'update_label' => 1,
          ],
          'rdb_settle' => [
            'title' => t('Radio Button Radicar'),
            'label' => 'Radicar',
            'service_field' => 'rdb_settle',
            'show' => 1,
            'update_label' => 1,
          ],
        ],
      ],
      'others_buttons' => [
        'table_fields' => [
          'link_definitions' => [
            'title' => 'Link de definiciones',
            'label' => 'Link de definiciones',
            'service_field' => 'link_definitions',
            'url' => '',
            'open_modal' => 1,
            'node_id' => 1,
            'target' => '_blank',
          ],
          'sic_link' => [
            'title' => 'Link SIC',
            'label' => 'Link SIC',
            'service_field' => 'sic_link',
            'url' => '',
            'target' => '_blank',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'scroll' => '10',
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
            'show_margin_internal_card' => 1,
          ],
          'logo' => [
            'image' => '',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $field['scroll'] = [
      '#type' => 'number',
      '#title' => t('Configure la cantidad de elementos del scroll'),
      '#description' => t('El valor debe ser mayor o igual a 10'),
      '#default_value' => $this->configuration['others']['config']['scroll'],
      '#min' => 10,
      '#required' => TRUE,
    ];

    $field['logo']['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Imagen para el link definiciones'),
      '#default_value' => $this->configuration['others']['config']['logo']['image'],
      '#description' => t('Por favor ingrese una imagen de formato PNG, JPEG, SVG, minimo 20px X 20px y maximo 60px X 60px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg svg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '60x60', $minimum_dimensions = '20x20'],
      ],
    ];

    // Table_options: fieldset que contiene todas las columnas de la tabla.
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de los campos'),
      '#open' => TRUE,
    ];

    $positions = [
      'left' => 'izquierda',
      'center' => 'centro',
      'right' => 'derecha',
    ];

    foreach ($positions as $key => $position) {
      $translatePositions = 'Campos a la ' . $position;
      $form['table_options'][$key] = [
        '#type' => 'details',
        '#title' => t($translatePositions),
        '#open' => TRUE,
      ];

      $form['table_options'][$key]['table_fields'] = [
        '#type' => 'table',
        '#header' => [
          t('Title'),
          t('Label'),
          t('Show'),
          t('Weight'),
          t('Espaciado'),
          '',
        ],
        '#empty' => t('There are no items yet. Add an item.'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'fields-order-weight',
          ],
        ],
      ];

      // Build table fields.
      $table_fields = $this->configuration['table_options'][$key]['table_fields'];

      // Se ordenan los filtros segun lo establecido en la configuración.
      uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      // Se crean todas las columnas de la tabla que mostrara la información.
      foreach ($table_fields as $id => $entity) {

        // TableDrag: Mark the table row as draggable.
        if ($id != 'image' && $id != 'client_name') {
          $form['table_options'][$key]['table_fields'][$id]['#attributes']['class'][] = 'draggable';
          // Sort the table row according to its existing/configured weight.
          $form['table_options'][$key]['table_fields']['#weight'] = $entity['weight'];
        }

        // Some table columns containing raw markup.
        $form['table_options'][$key]['table_fields'][$id]['title'] = [
          '#plain_text' => $entity['title'],
        ];

        // Some table columns containing raw markup.
        if (isset($entity['label'])) {
          $form['table_options'][$key]['table_fields'][$id]['label'] = [
            '#type' => 'textfield',
            '#default_value' => $entity['label'],
          ];
        }
        else {
          $form['table_options'][$key]['table_fields'][$id]['label'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

        $form['table_options'][$key]['table_fields'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['show'],
        ];

        // TableDrag: Weight column element.
        $form['table_options'][$key]['table_fields'][$id]['weight'] = [
          '#type' => 'weight',
          '#title' => t('Weight for @title', ['@title' => $entity['title']]),
          '#title_display' => 'invisible',
          '#default_value' => $entity['weight'],
          // Classify the weight element for #tabledrag.
          '#attributes' => ['class' => ['fields-order-weight']],
        ];

        if (isset($entity['class']) && $id != 'name_profile') {
          $form['table_options'][$key]['table_fields'][$id]['class'] = [
            '#type' => 'select',
            '#options' => [
              '1-columns' => t('Una columna'),
              '2-columns' => t('Dos columnas'),
              '3-columns' => t('Tres columnas'),
              '4-columns' => t('Cuatro columnas'),
              '5-columns' => t('Cinco columnas'),
              '6-columns' => t('Seis columnas'),
              '7-columns' => t('Siete columnas'),
              '8-columns' => t('Ocho columnas'),
              '9-columns' => t('Nueve columnas'),
              '10-columns' => t('Diez columnas'),
              '11-columns' => t('Once columnas'),
              '12-columns' => t('Doce columnas'),
            ],
            '#default_value' => $entity['class'],
          ];
        }
        else {
          $form['table_options'][$key]['table_fields'][$id]['class'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

        $form['table_options'][$key]['table_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }

    // Others_display: fieldset que contiene todas las columnas del expandible.
    $form['others_display'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones del detalle de la PQRs'),
      '#open' => TRUE,
    ];

    $positions = [
      'left' => 'izquierda',
      'right' => 'derecha',
    ];

    foreach ($positions as $key => $position) {
      $translatePositions = 'Campos a la ' . $position;
      $form['others_display'][$key] = [
        '#type' => 'details',
        '#title' => t($translatePositions),
        '#open' => TRUE,
      ];

      $form['others_display'][$key]['table_fields'] = [
        '#type' => 'table',
        '#header' => [
          t('Title'),
          t('Label'),
          t('Show'),
          t('Weight'),
          t('Espaciado'),
          '',
        ],
        '#empty' => t('There are no items yet. Add an item.'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'fields-order-weight',
          ],
        ],
      ];

      // Build table fields.
      $data_fields_details = $this->configuration['others_display'][$key]['table_fields'];

      // Se ordenan los filtros segun lo establecido en la configuración.
      uasort($data_fields_details, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

      // Se crean todas las columnas de la tabla que mostrara la información.
      foreach ($data_fields_details as $id => $entity) {
        // TableDrag: Mark the table row as draggable.
        $form['others_display'][$key]['table_fields'][$id]['#attributes']['class'][] = 'draggable';
        // TableDrag:
        // Sort the table row according to its existing/configured weight.
        $form['others_display'][$key]['table_fields']['#weight'] = $entity['weight'];
        // Some table columns containing raw markup.
        $form['others_display'][$key]['table_fields'][$id]['title'] = [
          '#plain_text' => $entity['title'],
        ];

        // Some table columns containing raw markup.
        if (isset($entity['label'])) {
          $form['others_display'][$key]['table_fields'][$id]['label'] = [
            '#type' => 'textfield',
            '#default_value' => $entity['label'],
          ];
        }
        else {
          $form['others_display'][$key]['table_fields'][$id]['label'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

        $form['others_display'][$key]['table_fields'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['show'],
        ];

        // TableDrag: Weight column element.
        $form['others_display'][$key]['table_fields'][$id]['weight'] = [
          '#type' => 'weight',
          '#title' => t('Weight for @title', ['@title' => $entity['title']]),
          '#title_display' => 'invisible',
          '#default_value' => $entity['weight'],
          // Classify the weight element for #tabledrag.
          '#attributes' => ['class' => ['fields-order-weight']],
        ];

        if (isset($entity['class'])) {
          $form['others_display'][$key]['table_fields'][$id]['class'] = [
            '#type' => 'select',
            '#options' => [
              '1-columns' => t('Una columna'),
              '2-columns' => t('Dos columnas'),
              '3-columns' => t('Tres columnas'),
              '4-columns' => t('Cuatro columnas'),
              '5-columns' => t('Cinco columnas'),
              '6-columns' => t('Seis columnas'),
              '7-columns' => t('Siete columnas'),
              '8-columns' => t('Ocho columnas'),
              '9-columns' => t('Nueve columnas'),
              '10-columns' => t('Diez columnas'),
              '11-columns' => t('Once columnas'),
              '12-columns' => t('Doce columnas'),
            ],
            '#default_value' => $entity['class'],
          ];
        }
        else {
          $form['others_display'][$key]['table_fields'][$id]['class'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

        $form['others_display'][$key]['table_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }

    $form = $this->instance->cardBlockForm($field, $form['table_options'], $form['others_display']);

    // Others_buttons: variable que contiene la configuracion
    // por defecto de los botones del sitio.
    $buttons = $this->configuration['others_buttons']['table_fields'];

    // others_buttons: fieldset que contiene todas las columnas de la tabla.
    $form['others_buttons'] = [
      '#type' => 'details',
      '#title' => t('Configurar otros links'),
      '#open' => TRUE,
    ];
    $form['others_buttons']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Title'),
        t('Label'),
        t('Url'),
        t('Abrir Modal'),
        t('Node Id'),
        t('Abrir en'),
      ],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    // Se crean todas las columnas de la tabla que mostrara la información.
    foreach ($buttons as $id => $entity) {

      // Some table columns containing raw markup.
      $form['others_buttons']['table_fields'][$id]['title'] = [
        '#plain_text' => $entity['title'],
      ];

      // Some table columns containing raw markup.
      if ($entity['label']) {
        $form['others_buttons']['table_fields'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
          '#size' => 40,
        ];
      }
      else {
        $form['others_buttons']['table_fields'][$id]['label'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['others_buttons']['table_fields'][$id]['url'] = [
        '#type' => 'textfield',
        '#description' => t('Ejemplo /links o http://www.tigoune.com/links'),
        '#default_value' => $entity['url'],
        '#size' => 20,
      ];

      if ($entity['open_modal']) {
        $form['others_buttons']['table_fields'][$id]['open_modal'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['open_modal'],
          '#description' => t('Si este campo esta activo no se abrira la url configurada'),
        ];
      }
      else {
        $form['others_buttons']['table_fields'][$id]['open_modal'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      if ($entity['node_id']) {
        $form['others_buttons']['table_fields'][$id]['node_id'] = [
          '#type' => 'number',
          '#default_value' => $entity['node_id'],
          '#size' => 20,
        ];
      }
      else {
        $form['others_buttons']['table_fields'][$id]['node_id'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['others_buttons']['table_fields'][$id]['target'] = [
        '#type' => 'select',
        '#options' => [
          '_blank' => t('Ventana nueva'),
          '_parent' => t('Ventana actual'),
        ],
        '#default_value' => $entity['target'],
      ];

      $form['others_buttons']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state, &$configuration) {
    $configuration['table_options'] = $form_state->getValue(['table_options']);
    $configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $configuration['others_display'] = $form_state->getValue(['others_display']);
    $configuration['buttons'] = $form_state->getValue(['buttons']);
    $configuration['others_buttons'] = $form_state->getValue(['others_buttons']);
    $configuration['others'] = $form_state->getValue(['others']);

    // Save image.
    $other_values = $form_state->getValue(['others', 'config']);
    $logo_image = $other_values['logo']['image'];
    if (isset($logo_image)) {
      $element = new UtilElement();
      $element->setFileAsPermanent($logo_image, 'tbo_services');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(QueryPqrsBlock &$instance, $configuration) {
    // Set data uuid, generate filters_fields, generate table_fields.
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'queryPqrsBlock');
    $instance->setValue('directive', 'data-ng-query-pqrs');
    $instance->setValue('class', 'block-query-pqrs');

    // Build filters.
    $labelUser = $labelRequestCode = $labelStatus = '';

    $filters = [];
    foreach ($instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];

        if ($key_filter == 'user') {
          $filters[$key_filter]['select_multiple_ng_options'] = TRUE;
          $labelUser = $filters[$key_filter]['label'];
        }

        if ($key_filter == 'request_code') {
          $filters[$key_filter]['select_multiple_ng_options'] = TRUE;
          $labelRequestCode = $filters[$key_filter]['label'];
        }

        if ($key_filter == 'status') {
          $filters[$key_filter]['select_multiple_ng_options'] = TRUE;
          $labelStatus = $filters[$key_filter]['label'];
        }

        if ($key_filter == 'exact_search') {
          $filters[$key_filter]['autocomplete_portafolio'] = TRUE;
        }
      }
    }

    $this->instance->setValue('filters', $filters);

    // Build columns table.
    // Ordering table_fields_left.
    $data_fields_left = $configuration['table_options']['left']['table_fields'];
    uasort($data_fields_left, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $data_left = [];
    foreach ($data_fields_left as $key_field => $field) {
      if ($field['show'] == 1) {
        $data_left[$key_field]['key'] = $key_field;
        $data_left[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $data_left[$key_field]['class'] = implode(" ", $classes);
        $data_left[$key_field]['class_field'] = $field['class'];
        $data_left[$key_field]['service_field'] = $field['service_field'];
        $data_left[$key_field]['position'] = $field['position'];
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Ordering table_fields_center.
    $data_fields_center = $configuration['table_options']['center']['table_fields'];
    uasort($data_fields_center, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $data_center = [];
    foreach ($data_fields_center as $key_field => $field) {
      if ($field['show'] == 1) {
        $data_center[$key_field]['key'] = $key_field;
        $data_center[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $data_center[$key_field]['class'] = implode(" ", $classes);
        $data_center[$key_field]['class_field'] = $field['class'];
        $data_center[$key_field]['service_field'] = $field['service_field'];
        $data_center[$key_field]['position'] = $field['position'];
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Ordering table_fields_center.
    $data_fields_right = $configuration['table_options']['right']['table_fields'];
    uasort($data_fields_right, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $data_right = [];
    foreach ($data_fields_right as $key_field => $field) {
      if ($field['show'] == 1) {
        $data_right[$key_field]['key'] = $key_field;
        $data_right[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $data_right[$key_field]['class'] = implode(" ", $classes);
        $data_right[$key_field]['class_field'] = $field['class'];
        $data_right[$key_field]['service_field'] = $field['service_field'];
        $data_right[$key_field]['position'] = $field['position'];
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Set columns.
    $instance->setValue('columns', $data_left);

    // Build columns details left.
    $details_profile_left = $configuration['others_display']['left']['table_fields'];
    uasort($details_profile_left, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $details_left = [];
    foreach ($details_profile_left as $key_field_left => $field_left) {
      if ($field_left['show'] == 1) {
        $details_left[$key_field_left]['key'] = $key_field_left;
        $details_left[$key_field_left]['label'] = $field_left['label'];
        $classes = ["field-" . $field_left['service_field']];
        $details_left[$key_field_left]['class'] = implode(" ", $classes);
        $details_left[$key_field_left]['class_field'] = $field_left['class'];
        $details_left[$key_field_left]['service_field'] = $field_left['service_field'];
        $details_left[$key_field_left]['position'] = $field_left['position'];
        unset($classes);
      }
      else {
        unset($field_left[$key_field_left]);
      }
    }

    // Build columns details right.
    $details_profile_right = $configuration['others_display']['right']['table_fields'];
    uasort($details_profile_right, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $details_right = [];
    foreach ($details_profile_right as $key_field => $field) {
      if ($field['show'] == 1) {
        $details_right[$key_field]['key'] = $key_field;
        $details_right[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $details_right[$key_field]['class'] = implode(" ", $classes);
        $details_right[$key_field]['class_field'] = $field['class'];
        $details_right[$key_field]['service_field'] = $field['service_field'];
        $details_right[$key_field]['position'] = $field['position'];
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Building var $build.
    $parameters = [
      'theme' => 'query_pqrs',
      'library' => 'tbo_services/query_pqrs',
    ];

    // Set title.
    $title = FALSE;
    if ($configuration['label_display'] == 'visible') {
      $title = $configuration['label'];
    }

    // Load node modal.
    $render = [];
    $others_buttons = $configuration['others_buttons']['table_fields'];
    if ($others_buttons['link_definitions']) {
      $open_modal = (int) $others_buttons['link_definitions']['open_modal'];
      $node_id = (int) $others_buttons['link_definitions']['node_id'];
      if ($open_modal && $node_id != 0) {
        $node = Node::load($node_id);

        if (isset($node)) {
          $render = \Drupal::entityTypeManager()
            ->getViewBuilder('node')
            ->view($node);
        }
      }
    }
    // Building class.
    $classes = [
      '12-columns' => 'col s12 m12',
      '11-columns' => 'col s11 m11',
      '10-columns' => 'col s10 m10',
      '9-columns' => 'col s9 m9',
      '8-columns' => 'col s8 m8',
      '7-columns' => 'col s7 m7',
      '6-columns' => 'col s6 m6',
      '5-columns' => 'col s5 m5',
      '4-columns' => 'col s4 m4',
      '3-columns' => 'col s3 m3',
      '2-columns' => 'col s2 m2',
      '1-columns' => 'col s1 m1',
    ];

    // Add image definitions link.
    $src = '';
    $file = File::load(reset($configuration['others']['config']['logo']['image']));
    if ($file) {
      $src = file_create_url($file->getFileUri());
    }

    $others = [
      '#buttons' => $configuration['buttons']['table_fields'],
      '#others_buttons' => $others_buttons,
      '#title' => $title,
      '#margin' => $configuration['others']['config']['show_margin'],
      '#columns_details_left' => $details_left,
      '#columns_details_right' => $details_right,
      '#columns_center' => $data_center,
      '#columns_right' => $data_right,
      '#classes' => $classes,
      '#render' => $render,
      '#message_data_empty' => t("No se encontraron resultados"),
      '#message_data_empty_rest' => t("No se encontraron datos en la consulta"),
      '#class' => $instance->getValue('class'),
      '#image_link_definitions' => $src,
    ];

    $instance->cardBuildVarBuild($parameters, $others);

    // Get environment.
    $environment = $_SESSION['company']['environment'];

    // Set value default to radio buttons is rdb_query->label because
    // It's not implemented to rdb_settle.
    // Add other_config directive.
    $other_config = [
      'scroll' => $configuration['others']['config']['scroll'],
      'text_btn_detail_normal' => $configuration['buttons']['table_fields']['detail']['label'],
      'text_btn_detail_expanded' => t('VER MENOS'),
      'labelUser' => $labelUser,
      'labelRequestCode' => $labelRequestCode,
      'labelStatus' => $labelStatus,
      'environment_enterprise' => $environment,
      'type' => 'consultar',
    ];

    // Set config_block.
    $config_block = $instance->cardBuildConfigBlock('/tbo_services/rest/query-pqrs?_format=json', $other_config);

    // Add configuration drupal.js object.
    $instance->cardBuildAddConfigDirective($config_block, $instance->getValue('config_name'));

    // Save audit log.
    $this->saveAuditLog();

    // Send segment track.
    $this->segmentTrack($environment);

    return $instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

  /**
   * Implements function saveAuditLog().
   */
  public function saveAuditLog() {
    try {
      // Save audit log.
      $log = \Drupal::service('tbo_core.audit_log_service');
      $log->loadName();
      // Create array data_log.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => 'PQRs',
        'description' => t('Usuario accede a la consulta de PQRs'),
        'details' => t('Usuario @userName accede a consulta de PQRs',
          [
            '@userName' => $log->getName(),
          ]
        ),
        'old_value' => 'No disponible',
        'new_value' => 'No disponible',
      ];
      // Save audit log.
      $log->insertGenericLog($data_log);
    }
    catch (\Exception $e) {
      // Save drupal log.
    }

  }

  /**
   * Implements segmentTrack().
   *
   * @param string $environment
   *   The environment type.
   */
  public function segmentTrack($environment = '') {
    if ($environment == 'both') {
      $environment = 'movil - fijo';
    }
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $segment = $service_segment->getSegmentPhp();
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
    if (isset($tigoId)) {
      try {
        $segment_track = [
          'event' => 'TBO - Consulta Pqrs - Consulta',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pqrs',
            'label' => $environment,
            'site' => 'NEW',
          ],
        ];

        $segment->track($segment_track);
      }
      catch (\Exception $e) {
        // Save Drupal log.
      }
    }

  }

}
