<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_services\Plugin\Block\QueryTechnicalSupportOrdersBlock;

/**
 * Manage config a 'QueryTechnicalSupportOrdersBlockClass' block.
 */
class QueryTechnicalSupportOrdersBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Set instance configuration block.
   *
   * @param \Drupal\tbo_services\Plugin\Block\QueryTechnicalSupportOrdersBlock $instance
   *   Instance SearchByProfileBlock block.
   * @param array $config
   *   Instance config block.
   */
  public function setConfig(QueryTechnicalSupportOrdersBlock &$instance, array &$config) {
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
          'order_number' => [
            'title' => 'Número de orden',
            'label' => 'Número de orden',
            'service_field' => 'order_number',
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
          'line_number' => [
            'title' => 'Número de linea',
            'label' => 'Número de linea',
            'service_field' => 'line_number',
            'show' => 1,
            'weight' => 4,
            'class' => '3-columns',
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'card_technical' => [
            'title' => 'Card Technical Suppor',
            'service_field' => 'card_technical',
            'show' => 1,
            'weight' => 1,
          ],
        ],
        'left' => [
          'table_fields' => [
            'line_number' => [
              'title' => 'Número de línea',
              'label' => 'Número de línea',
              'service_field' => 'line_number',
              'position' => 'left',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 1,
            ],
            'order' => [
              'title' => 'Orden',
              'label' => 'Orden',
              'service_field' => 'order',
              'position' => 'left',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 2,
            ],
            'status' => [
              'title' => 'Estado',
              'service_field' => 'status',
              'position' => 'left',
              'class' => '12-columns',
              'show' => 1,
              'weight' => 3,
            ],
          ],
        ],
        'center' => [
          'table_fields' => [
            'date' => [
              'title' => 'Fecha de creación',
              'label' => 'Fecha de creación',
              'service_field' => 'date',
              'position' => 'center',
              'class' => '3-columns',
              'show' => 1,
              'weight' => 1,
            ],
            'email' => [
              'title' => 'Correo electrónico',
              'label' => 'Correo electrónico',
              'service_field' => 'email',
              'position' => 'center',
              'class' => '6-columns',
              'show' => 1,
              'weight' => 2,
            ],
            'city' => [
              'title' => 'Ciudad',
              'label' => 'Ciudad',
              'service_field' => 'city',
              'position' => 'center',
              'class' => '3-columns',
              'show' => 1,
              'weight' => 3,
            ],
          ],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'card_technical' => [
            'title' => 'Card Details Pqrs',
            'service_field' => 'card_technical',
            'show' => 1,
            'weight' => 1,
          ],
        ],
        'left' => [
          'table_fields' => [
            'serviceCollection_information' => [
              'title' => t("Mostrar datos alineados a la izquierda"),
              'service_field' => 'serviceCollection_information',
              'weight' => 1,
              'show' => 1,
            ],
            'service_center' => [
              'title' => 'Centro de servicios',
              'label' => 'Centro de servicios',
              'service_field' => 'service_center',
              'show' => 1,
              'weight' => 2,
              'position' => 'left',
            ],
            'model' => [
              'title' => 'Modelo',
              'label' => 'Modelo',
              'service_field' => 'model',
              'show' => 1,
              'weight' => 3,
              'position' => 'left',
            ],
            'imei' => [
              'title' => 'Imei',
              'label' => 'Imei',
              'service_field' => 'imei',
              'show' => 1,
              'weight' => 4,
              'position' => 'left',
            ],
            'accessories' => [
              'title' => 'Accesorios',
              'label' => 'Accesorios',
              'service_field' => 'accessories',
              'show' => 1,
              'weight' => 5,
              'position' => 'left',
            ],
            're_inside' => [
              'title' => 'Reingreso',
              'label' => 'Reingreso',
              'service_field' => 're_inside',
              'show' => 1,
              'weight' => 6,
              'position' => 'left',
            ],
          ],
        ],
        'right' => [
          'table_fields' => [
            'description' => [
              'title' => 'Descripción',
              'label' => 'Descripción',
              'service_field' => 'description',
              'show' => 1,
              'weight' => 2,
            ],
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'detail' => [
            'title' => t('Botón ver detalles'),
            'label' => 'VER TODO',
            'service_field' => 'detail',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
          'download' => [
            'title' => t('Botón Descargar'),
            'label' => t("Descargar"),
            'service_field' => 'download',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
            'type_report' => 'csv',
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
          'labels_download' => [
            'name' => 'Nombre',
            'identification' => 'Identificación',
            'order' => 'Número de orden',
            'date' => 'Fecha Creación',
            'status' => 'Estado',
            'line_number' => 'Número de línea',
            'email' => 'Correo electrónico',
            'city' => 'Ciudad',
            'service_center' => 'Centro de servicio',
            'model' => 'Modelo',
            'imei' => 'IMEI',
            're_inside' => 'Reingreso',
            'description' => 'Descripción',
            'accessories' => 'Accesorios',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $fields['scroll'] = [
      '#type' => 'number',
      '#title' => t('Configure la cantidad de elementos del scroll'),
      '#description' => t('El valor debe ser mayor o igual a 10'),
      '#default_value' => $this->configuration['others']['config']['scroll'],
      '#min' => 10,
      '#required' => TRUE,
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
        if ($id != 'line_number') {
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

        if (isset($entity['class']) && $key != 'left') {
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
      '#title' => t('Configuraciones del detalle de la Orden'),
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

      // Se ordenan los campos segun lo establecido en la configuración.
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

        $form['others_display'][$key]['table_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }

    $labels_download = $this->configuration['others']['config']['labels_download'];
    $fields['labels_download'] = [
      '#type' => 'details',
      '#title' => t('Nombres de los campos para exportar'),
      '#open' => TRUE,
    ];

    foreach ($labels_download as $label => $label_value) {
      $fields['labels_download'][$label] = [
        '#type' => 'textfield',
        '#title' => $label_value,
        '#default_value' => $labels_download[$label],
      ];
    }

    $form = $this->instance->cardBlockForm($fields, $form['table_options'], $form['others_display']);

    // Buttons.
    // Others_buttons: Get config buttons.
    $buttons = $this->configuration['buttons']['table_fields'];

    if (!empty($buttons)) {
      // Others_buttons: fieldset que contiene todas las columnas de la tabla.
      $form['buttons'] = [
        '#type' => 'details',
        '#title' => t('Configuraciones de los botones'),
        '#open' => TRUE,
      ];
      $form['buttons']['table_fields'] = [
        '#type' => 'table',
        '#header' => [
          t('Title'),
          t('Label'),
          t('Type of format'),
          t('Show'),
          t('Active'),
          '',
        ],
        '#empty' => t('There are no items yet. Add an item.'),
      ];

      // Se crean todas las columnas de la tabla que mostrara la información.
      foreach ($buttons as $id => $entity) {

        // Some table columns containing raw markup.
        $form['buttons']['table_fields'][$id]['title'] = [
          '#plain_text' => $entity['title'],
        ];

        // Some table columns containing raw markup.
        if ($entity['update_label']) {
          $form['buttons']['table_fields'][$id]['label'] = [
            '#type' => 'textfield',
            '#default_value' => $entity['label'],
            '#size' => 40,
          ];
        }
        else {
          $form['buttons']['table_fields'][$id]['label'] = [
            '#type' => 'label',
            '#default_value' => $entity['label'],
          ];
        }

        if ($entity['service_field'] == 'download') {
          $form['buttons']['table_fields'][$id]['type_report'] = [
            '#type' => 'select',
            '#options' => [
              'csv' => t('csv'),
              'xlsx' => t('excel'),
              'txt' => t('texto'),
            ],
            '#default_value' => $entity['type_report'],
          ];
        }
        else {
          $form['buttons']['table_fields'][$id]['type_report'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

        $form['buttons']['table_fields'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['show'],
        ];

        if (isset($entity['active'])) {
          $form['buttons']['table_fields'][$id]['active'] = [
            '#type' => 'checkbox',
            '#default_value' => $entity['active'],
          ];
        }

        $form['buttons']['table_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(QueryTechnicalSupportOrdersBlock &$instance, $configuration) {
    // Set data uuid, generate filters_fields, generate table_fields.
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'queryTechnicalSupportBlock');
    $instance->setValue('directive', 'data-ng-query-technical-support');
    $instance->setValue('class', 'block-query-technical-support');

    // Build filters.
    $labelOrderNumber = $labelLineNumber = $labelStatus = '';

    // Ordering table_fields_left.
    $data_filters = $instance->getValue('filters_fields');
    uasort($data_filters, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $filters = [];
    foreach ($data_filters as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];

        if ($key_filter == 'order_number') {
          $filters[$key_filter]['select_multiple_ng_options'] = TRUE;
          $labelOrderNumber = $filters[$key_filter]['label'];
        }
        elseif ($key_filter == 'status') {
          $filters[$key_filter]['select_multiple_ng_options'] = TRUE;
          $labelStatus = $filters[$key_filter]['label'];
        }
        elseif ($key_filter == 'line_number') {
          $filters[$key_filter]['select_multiple_ng_options'] = TRUE;
          $labelLineNumber = $filters[$key_filter]['label'];
        }
        elseif ($key_filter == 'exact_search') {
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
      'theme' => 'query_technical_support',
      'library' => 'tbo_services/query_technical_support',
    ];

    // Set title.
    $title = FALSE;
    if ($configuration['label_display'] == 'visible') {
      $title = $configuration['label'];
    }

    // Load node modal.
    $others_buttons = $configuration['others_buttons']['table_fields'];

    // Building class.
    $classes = [
      '12-columns' => 'col s12 m12 l12',
      '11-columns' => 'col s11 m11 l11',
      '10-columns' => 'col s10 m10 l10',
      '9-columns' => 'col s9 m9 l9',
      '8-columns' => 'col s8 m8 l8',
      '7-columns' => 'col s7 m7 l7',
      '6-columns' => 'col s6 m6 l6',
      '5-columns' => 'col s5 m5 l5',
      '4-columns' => 'col s4 m4 l4',
      '3-columns' => 'col s3 m3 l3',
      '2-columns' => 'col s2 m2 l2',
      '1-columns' => 'col s1 m1 l1',
    ];

    $others = [
      '#buttons' => $configuration['buttons']['table_fields'],
      '#others_buttons' => $others_buttons,
      '#title' => $title,
      '#margin' => $configuration['others']['config']['show_margin'],
      '#columns_details_left' => $details_left,
      '#columns_details_right' => $details_right,
      '#columns_center' => $data_center,
      '#classes' => $classes,
      '#message_data_empty' => t("No se encontraron resultados"),
      '#message_data_empty_rest' => t("No se encontraron datos en la consulta"),
      '#class' => $instance->getValue('class'),
    ];

    $instance->cardBuildVarBuild($parameters, $others);

    // Set value default to radio buttons is rdb_query->label because
    // It's not implemented to rdb_settle.
    // Add other_config directive.
    $other_config = [
      'scroll' => $configuration['others']['config']['scroll'],
      'text_btn_detail_normal' => $configuration['buttons']['table_fields']['detail']['label'],
      'text_btn_detail_expanded' => t('VER MENOS'),
      'labelOrderNumber' => $labelOrderNumber,
      'labelLineNumber' => $labelLineNumber,
      'labelStatus' => $labelStatus,
    ];

    // Set config_block.
    $config_block = $instance->cardBuildConfigBlock('/tbo_services/rest/query-technical-support?_format=json', $other_config);

    // Add configuration drupal.js object.
    $instance->cardBuildAddConfigDirective($config_block, $instance->getValue('config_name'));

    // Save audit log.
    $this->saveAuditLog();

    // Send segment track.
    $this->segmentTrack();

    // Set session config labels export.
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
    if ($document_number != '') {
      $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_technical_support');
      $labels_export = $configuration['others']['config']['labels_download'];
      $tempStore->set('tbo_query_technical_support_labels_' . md5($document_number), $labels_export);
    }

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
        'event_type' => 'Soporte Técnico',
        'description' => t('Usuario accede a la consulta de órdenes de soporte técnico'),
        'details' => t('Usuario @userName accede a consulta de órdenes de soporte técnico',
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
   */
  public function segmentTrack() {
    $event = 'TBO - Consulta STM - Consulta';
    $category = 'Soporte Técnico Móvil';
    $label = 'movil';
    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
  }

}
