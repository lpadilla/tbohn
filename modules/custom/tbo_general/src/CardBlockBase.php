<?php

namespace Drupal\tbo_general;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CardBlockBase.
 *
 * @package Drupal\tbo_general
 */
class CardBlockBase extends BlockBase implements CardBlockBaseInterface {

  protected $build = [];

  protected $columns = [];

  protected $class = 'block-white';

  protected $config_name = 'b2bBlock';

  protected $config_pager = [];

  protected $directive = '';

  protected $filters = [];

  protected $filters_fields = [];

  protected $headers_table_query = [];

  protected $table_fields = [];

  protected $uuid = NULL;

  protected $dataAngular = '';

  protected $fieldOrder = [];

  /**
   * Builds the Card block form.
   *
   * @param array $others
   *   Other options array.
   * @param array $table_options
   *   Table options configuration.
   * @param array $others_display_options
   *   Other display options.
   *
   * @return mixed
   *   Return form instance.
   */
  public function cardBlockForm($others = [], $table_options = [], $others_display_options = []) {
    // filters_fields: variable que contendrá la configuración
    // por defecto de los filtros.
    $filters_fields = $this->configuration['filters_options']['filters_fields'];
    if (!empty($filters_fields)) {
      $form['filters_options'] = [
        '#type' => 'details',
        '#title' => $this->t('Opciones de los filtros'),
        '#open' => TRUE,
      ];

      // Crear el fieldset que contiene los filtros de la tabla.
      $form['filters_options'] = [
        '#type' => 'details',
        '#title' => $this->t('Opciones de los filtros'),
        '#open' => TRUE,
      ];

      $form['filters_options']['filters_fields'] = [
        '#type' => 'table',
        '#header' => [
          t('Field'),
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

      // Se ordenan los filtros segun lo establecido en la configuración.
      uasort($filters_fields, [
        'Drupal\Component\Utility\SortArray',
        'sortByWeightElement',
      ]);

      // Se crean todos los campos de filtro que contendrá el bloque.
      foreach ($filters_fields as $id => $entity) {
        // TableDrag: Mark the table row as draggable.
        $form['filters_options']['filters_fields'][$id]['#attributes']['class'][] = 'draggable';

        // TableDrag: Sort the table row according to its
        // existing/configured weight.
        $form['filters_options']['filters_fields']['#weight'] = $entity['weight'];

        // Some table columns containing raw markup.
        if (isset($entity['label'])) {
          // Some table columns containing raw markup.
          $form['filters_options']['filters_fields'][$id]['title'] = [
            '#plain_text' => $entity['title'],
          ];

          $form['filters_options']['filters_fields'][$id]['label'] = [
            '#type' => 'textfield',
            '#default_value' => $entity['label'],
          ];
        }
        else {
          // Some table columns containing raw markup.
          $form['filters_options']['filters_fields'][$id]['label'] = [
            '#plain_text' => $entity['title'],
          ];

          $form['filters_options']['filters_fields'][$id]['none'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

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

        if (isset($entity['class'])) {
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
        }
        else {
          $form['table_options']['table_fields'][$id]['class'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }
        $form['filters_options']['filters_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }
    // $table_fields: variable que contiene la configuracion
    // por defecto de las columnas de la tabla.
    if (isset($this->configuration['table_options']['table_fields'])) {
      $table_fields = $this->configuration['table_options']['table_fields'];
    }
    if (!empty($table_fields)) {
      // Validate var $table_options.
      if (empty($table_options)) {
        // table_options: fieldset que contiene todas las columnas de la tabla.
        $form['table_options'] = [
          '#type' => 'details',
          '#title' => $this->t('Configuraciones tabla'),
          '#open' => TRUE,
        ];

        $form['table_options']['table_fields'] = [
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

        // Hidden column espaciado.
        if (isset($this->configuration['not_show_class']['columns'])) {
          $form['table_options']['table_fields']['#header'] = [
            t('Title'),
            t('Label'),
            t('Show'),
            t('Weight'),
            '',
          ];
          if (isset($this->configuration['orderly'])) {
            $form['table_options']['table_fields']['#header'] = [
              t('Title'),
              t('Label'),
              t('Show'),
              t('Orderly'),
              t('Type Order'),
              t('Weight'),
              '',
            ];
          }
        }
        else {
          if (isset($this->configuration['orderly'])) {
            $form['table_options']['table_fields']['#header'] = [
              t('Title'),
              t('Label'),
              t('Show'),
              t('Orderly'),
              t('Type Order'),
              t('Weight'),
              t('Espaciado'),
              '',
            ];
          }
        }

        // Se ordenan los filtros segun lo establecido en la configuración.
        uasort($table_fields, [
          'Drupal\Component\Utility\SortArray',
          'sortByWeightElement',
        ]);
        // Se crean todas las columnas de la tabla que mostrara la información.
        foreach ($table_fields as $id => $entity) {
          // TableDrag: Mark the table row as draggable.
          $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';

          // TableDrag: Sort the table row according to its
          // existing/configured weight.
          $form['table_options']['table_fields']['#weight'] = $entity['weight'];

          // Some table columns containing raw markup.
          $form['table_options']['table_fields'][$id]['title'] = [
            '#plain_text' => $entity['title'],
          ];

          // Some table columns containing raw markup.
          if (isset($entity['label'])) {
            $form['table_options']['table_fields'][$id]['label'] = [
              '#type' => 'textfield',
              '#default_value' => $entity['label'],
            ];
          }
          else {
            $form['table_options']['table_fields'][$id]['label'] = [
              '#type' => 'label',
              '#default_value' => '',
            ];
          }

          $form['table_options']['table_fields'][$id]['show'] = [
            '#type' => 'checkbox',
            '#default_value' => $entity['show'],
          ];

          if (isset($entity['orderly'])) {
            $form['table_options']['table_fields'][$id]['orderly'] = [
              '#type' => 'checkbox',
              '#default_value' => $entity['orderly'],
            ];

            if (isset($entity['type_order'])) {
              $form['table_options']['table_fields'][$id]['type_order'] = [
                '#type' => 'select',
                '#options' => [
                  'string' => $this->t('Cadena'),
                  'number' => $this->t('Numero'),
                  'date' => $this->t('Fecha'),
                ],
                '#default_value' => $entity['type_order'],
              ];
            }
          }

          // TableDrag: Weight column element.
          $form['table_options']['table_fields'][$id]['weight'] = [
            '#type' => 'weight',
            '#title' => t('Weight for @title', ['@title' => $entity['title']]),
            '#title_display' => 'invisible',
            '#default_value' => $entity['weight'],
            // Classify the weight element for #tabledrag.
            '#attributes' => ['class' => ['fields-order-weight']],
          ];

          if (isset($entity['class'])) {
            $form['table_options']['table_fields'][$id]['class'] = [
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
          }
          else {
            $form['table_options']['table_fields'][$id]['class'] = [
              '#type' => 'label',
              '#default_value' => '',
            ];
          }

          $form['table_options']['table_fields'][$id]['service_field'] = [
            '#type' => 'hidden',
            '#value' => $entity['service_field'],
          ];
        }
      }
      else {
        $form['table_options'] = $table_options;
      }
    }

    // $others_display: variable que contiene la configuracion por defecto
    // de las configuraciones adicionales del sitio con estados activo.
    $others_display = $this->configuration['others_display']['table_fields'];

    if (!empty($others_display)) {
      if (empty($others_display_options)) {
        // table_options: fieldset que contiene todas las columnas de la tabla.
        $form['others_display'] = [
          '#type' => 'details',
          '#title' => $this->t('Configuraciones Adicionales con estado'),
          '#open' => TRUE,
        ];
        $form['others_display']['table_fields'] = [
          '#type' => 'table',
          '#header' => [t('Title'), t('Label'), t('Show'), t('Active'), ''],
          '#empty' => t('There are no items yet. Add an item.'),
        ];

        // Se crean todas las columnas de la tabla que mostrara la información.
        foreach ($others_display as $id => $entity) {

          // Some table columns containing raw markup.
          $form['others_display']['table_fields'][$id]['title'] = [
            '#plain_text' => $entity['title'],
          ];

          // Some table columns containing raw markup.
          if (isset($entity['not_update_label'])) {
            $form['others_display']['table_fields'][$id]['label'] = [
              '#type' => 'label',
              '#default_value' => $entity['label'],
            ];
          }
          else {
            $form['others_display']['table_fields'][$id]['label'] = [
              '#type' => 'textfield',
              '#default_value' => $entity['label'],
            ];
          }

          $form['others_display']['table_fields'][$id]['show'] = [
            '#type' => 'checkbox',
            '#default_value' => $entity['show'],
          ];

          if (isset($entity['active'])) {
            $form['others_display']['table_fields'][$id]['active'] = [
              '#type' => 'checkbox',
              '#default_value' => $entity['active'],
            ];
          }

          $form['others_display']['table_fields'][$id]['service_field'] = [
            '#type' => 'hidden',
            '#value' => $entity['service_field'],
          ];
        }
      }
      else {
        $form['others_display'] = $others_display_options;
      }
    }

    // others_buttons: variable que contiene la configuracion
    // por defecto de los botones del sitio.
    $buttons = $this->configuration['buttons']['table_fields'];

    if (!empty($buttons)) {
      // others_buttons: fieldset que contiene todas las columnas de la tabla.
      $form['buttons'] = [
        '#type' => 'details',
        '#title' => $this->t('Configuraciones de los botones'),
        '#open' => TRUE,
      ];
      $form['buttons']['table_fields'] = [
        '#type' => 'table',
        '#header' => [
          t('Title'),
          t('Label'),
          t('Url'),
          t('Show'),
          t('Active'),
          '',
        ],
        '#empty' => t('There are no items yet. Add an item.'),
      ];

      // Se crean todas las columnas de la tabla que mostrará la información.
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

        if (isset($entity['url'])) {
          $form['buttons']['table_fields'][$id]['url'] = [
            '#type' => 'textfield',
            '#description' => isset($entity['url_description']) ? $entity['url_description'] : '',
            '#default_value' => $entity['url'],
            '#size' => 20,
          ];
        }
        else {
          $form['buttons']['table_fields'][$id]['url'] = [
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

    if (isset($this->configuration['others']['config'])) {
      $others_config = $this->configuration['others']['config'];
    }

    if (!empty($others_config)) {
      $form['others'] = [
        '#type' => 'details',
        '#title' => $this->t('Otras configuraciones'),
        '#open' => TRUE,
      ];

      if (!empty($others_config['paginate'])) {
        $form['others']['config']['paginate'] = [
          '#type' => 'details',
          '#title' => $this->t('Configurar Paginador'),
          '#open' => TRUE,
        ];

        if (!empty($others_config['paginate']['number_pages'])) {
          $form['others']['config']['paginate']['number_pages'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Numero de Paginas a mostrar'),
            '#default_value' => $others_config['paginate']['number_pages'],
          ];
        }

        if (!empty($others_config['paginate']['number_rows_pages'])) {
          $form['others']['config']['paginate']['number_rows_pages'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Numero de elementos a mostrar por pagina'),
            '#default_value' => $others_config['paginate']['number_rows_pages'],
          ];
        }
      }

      if (!empty($others_config['show_margin'])) {
        $form['others']['config']['show_margin'] = [
          '#type' => 'details',
          '#title' => $this->t('Configurar Margenes del card'),
          '#open' => TRUE,
        ];

        if (isset($others_config['show_margin']['show_margin_filter'])) {
          $form['others']['config']['show_margin']['show_margin_filter'] = [
            '#type' => 'checkbox',
            '#default_value' => $others_config['show_margin']['show_margin_filter'],
            '#title' => $this->t('Agregar margen al card de filtros'),
          ];
        }

        if (isset($others_config['show_margin']['show_margin_card'])) {
          $form['others']['config']['show_margin']['show_margin_card'] = [
            '#type' => 'checkbox',
            '#default_value' => $others_config['show_margin']['show_margin_card'],
            '#title' => $this->t('Agregar margen al card de datos'),
          ];
        }

        if (isset($others_config['show_margin']['show_margin_top_content_card'])) {
          $form['others']['config']['show_margin']['show_margin_top_content_card'] = [
            '#type' => 'checkbox',
            '#default_value' => $others_config['show_margin']['show_margin_top_content_card'],
            '#title' => $this->t('Agregar margen al bloque que se encuentra entre los filtros y datos'),
          ];
        }

        if (isset($others_config['show_margin']['show_margin_internal_card'])) {
          $form['others']['config']['show_margin']['show_margin_internal_card'] = [
            '#type' => 'checkbox',
            '#default_value' => $others_config['show_margin']['show_margin_internal_card'],
            '#title' => $this->t('Agregar margen a cada card dentro del card principal de datos'),
          ];
        }
      }

      if (!empty($others)) {
        foreach ($others as $key => $value) {
          $form['others']['config'][$key] = $value;
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['table_options'] = $form_state->getValue(['table_options']);
    $this->configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $this->configuration['others_display'] = $form_state->getValue(['others_display']);
    $this->configuration['buttons'] = $form_state->getValue(['buttons']);
    $this->configuration['others'] = $form_state->getValue(['others']);
  }

  /**
   * Implements method cardBuildHeader().
   *
   * $header[uuid] => el id unico del display del bloque
   * $header[filters_fields] =>
   *   Contiene la configuracion de los filtros del bloque
   * $header[table_fields] => Contiene la configuracion
   *   de las columnas a mostrar en la tabla.
   *
   * @param bool $filters
   *   Filters flag to set the filters or not.
   * @param bool $columns
   *   Columns flag to set the columns or not.
   */
  public function cardBuildHeader($filters = TRUE, $columns = TRUE) {
    // TODO validar porque algunos bloques no tienen uuid.
    $this->uuid = isset($this->configuration['uuid']) ? $this->configuration['uuid'] : md5('test' . rand(0, 500));
    $this->configuration['uuid'] = isset($this->configuration['uuid']) ? $this->configuration['uuid'] : $this->uuid;
    $this->filters_fields = $this->configuration['filters_options']['filters_fields'];
    $this->table_fields = $this->configuration['table_options']['table_fields'];

    // Set filters.
    if ($filters) {
      // Sort filters_fields and table_fields.
      $this->cardSortArray($this->filters_fields);
      $this->cardBuildFilters($this->filters_fields);
    }

    if ($columns) {
      $this->cardSortArray($this->table_fields);
      $this->cardBuilderColumns($this->table_fields);
    }
  }

  /**
   * Card build filters configuration.
   *
   * @param array $array_filters
   *   Filters info.
   *
   * @return array
   *   New filters info.
   */
  public function cardBuildFilters($array_filters = []) {
    $filters = [];
    foreach ($array_filters as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = isset($filter['label']) ? $filter['label'] : $filter['title'];
        $classes = [
          "field-" . $filter['service_field'],
          isset($filter['class']) ? $filter['class'] : '',
        ];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];

        if (isset($filter['input_type'])) {
          $filters[$key_filter]['input_type'] = $filter['input_type'];
        }

        if (!empty($filter['validate_length'])) {
          $filters[$key_filter]['validate_length'] = $filter['validate_length'];
        }

        if (isset($filter['autocomplete'])) {
          $filters[$key_filter]['autocomplete'] = $filter['autocomplete'];
        }

        if (isset($filter['type'])) {
          $filters[$key_filter]['type'] = $filter['type'];
        }

        if (isset($filter['pattern'])) {
          $filters[$key_filter]['pattern'] = $filter['pattern'];
        }

        if (isset($filter['oninvalid'])) {
          $filters[$key_filter]['oninvalid'] = $filter['oninvalid'];
        }
      }
    }

    $this->filters = $filters;

    return $filters;
  }

  /**
   * Configure card builder columns.
   *
   * @param array $array_columns
   *   Columns info.
   */
  public function cardBuilderColumns($array_columns = []) {
    $columns = [];
    $counter = 0;

    foreach ($array_columns as $key_field => $field) {
      if ($field['show'] == 1) {
        $columns[$key_field]['key'] = $key_field;
        $columns[$key_field]['title'] = $field['title'];
        $columns[$key_field]['label'] = isset($field['label']) ? $field['label'] : $field['title'];
        $classes = [
          "field-" . $field['service_field'],
          isset($field['class']) ? $field['class'] : '',
        ];

        $columns[$key_field]['class'] = implode(" ", $classes);
        $columns[$key_field]['service_field'] = $field['service_field'];
        $columns[$key_field]['position'] = $field['weight'];

        if (isset($field['type'])) {
          $columns[$key_field]['type'] = $field['type'];
        }

        if (isset($field['orderly']) && $field['orderly']) {
          $columns[$key_field]['orderly'] = isset($field['type_order']) ? $field['type_order'] : 'string';
          if ($counter == 0) {
            $columns[$key_field]['orderly_first'] = 1;
            $fieldOrder = [
              'service_field' => $field['service_field'],
              'type_order' => isset($field['type_order']) ? $field['type_order'] : 'string',
            ];
            $this->fieldOrder = $fieldOrder;
            $counter++;
          }
        }

        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    $this->columns = $columns;
  }

  /**
   * Sorts the card components by weight.
   *
   * @param array $array
   *   Sorted card info.
   */
  public function cardSortArray(&$array) {
    uasort($array, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);
  }

  /**
   * Stores in TempStore some card info.
   *
   * @param bool $table
   *   Table info flag.
   * @param array $others
   *   Other info to store.
   * @param string $module
   *   Module name for storing values.
   */
  public function cardBuildSession($table = TRUE, $others = [], $module = '') {
    // Se obtiene el servicio drupal para almacenar valores temporales y se
    // crear la variable que contiene las columnas a llamar en el query sql.
    if ($module == '') {
      $tempstore = \Drupal::service('user.private_tempstore')
        ->get('tbo_account');
    }
    else {
      $tempstore = \Drupal::service('user.private_tempstore')->get($module);
    }

    if ($table) {
      if (empty($this->headers_table_query)) {
        $this->headers_table_query = $this->columns;
      }
      $tempstore->set($this->config_name . $this->uuid, $this->headers_table_query);
      if (isset($this->configuration['others']['config']['paginate'])) {
        $tempstore->set($this->config_name . '_pager' . $this->uuid, $this->configuration['others']['config']['paginate']);
      }
    }

    if (!empty($others)) {
      foreach ($others as $key => $valor) {
        $tempstore->set($this->config_name . $key['name'] . $this->uuid, $key['value']);
      }
    }
  }

  /**
   * Builds the card build array.
   *
   * @param array $parameters
   *   Parameters for the build array.
   * @param array $others
   *   Extra parameters.
   */
  public function cardBuildVarBuild($parameters = [], $others = []) {
    if (empty($parameters['columns'])) {
      $parameters['columns'] = $this->columns;
    }

    $build = [
      '#theme' => $parameters['theme'],
      '#uuid' => $this->uuid,
      '#directive' => $this->directive,
      '#fields' => $parameters['columns'],
      '#filters' => $this->filters,
      '#class' => $this->class,
      '#plugin_id' => $this->getPluginId(),
    ];

    if (isset($parameters['library'])) {
      $build['#attached'] = [
        'library' => [
          $parameters['library'],
        ],
      ];
    }

    if (!empty($others)) {
      foreach ($others as $key => $value) {
        $build[$key] = $value;
      }
    }
    $build['#cache']['max-age'] = 0;
    $this->build = $build;
  }

  /**
   * Builds the config block array.
   *
   * @param string $endpoint
   *   Endpoint URL.
   * @param array $others
   *   Extra parameter for the config block.
   *
   * @return array
   *   Config block info.
   */
  public function cardBuildConfigBlock($endpoint = NULL, $others = []) {
    $config_block = [
      'url' => $endpoint,
      'uuid' => $this->uuid,
      'filters' => $this->filters,
      'config_pager' => isset($this->configuration['others']['config']['paginate']) ? $this->configuration['others']['config']['paginate'] : 0,
      'config_name' => $this->config_name,
    ];

    if (!empty($others)) {
      foreach ($others as $key => $value) {
        $config_block[$key] = $value;
      }
    }

    return $config_block;
  }

  /**
   * Builds the card form.
   *
   * @param \Drupal\Core\Form\FormInterface|string $route
   *   Name of the route or the Form object.
   *
   * @return array
   *   The form array.
   */
  public function cardBuildGetForm($route = NULL) {
    $form = \Drupal::formBuilder()->getForm($route);

    return $form;
  }

  /**
   * Adds to the build array the config directive.
   *
   * @param array $config
   *   Config info array.
   * @param string $name
   *   Block name.
   * @param array $others
   *   Extra info.
   */
  public function cardBuildAddConfigDirective($config = [], $name = NULL, $others = []) {
    if (isset($name)) {
      $this->build['#attached']['drupalSettings'][$name][$this->uuid] = $config;
    }
    else {
      $this->build['#attached']['drupalSettings']['b2bBlock'][$this->uuid] = $config;
    }

    if (!empty($others)) {
      foreach ($others as $key => $data) {
        $this->build['#attached']['drupalSettings'][$key][$this->uuid] = $data;
      }
    }
  }

  /**
   * Sets the filters.
   *
   * @param array $filters
   *   Filters info.
   */
  public function setFilters($filters = []) {
    $this->filters = $filters;
  }

  /**
   * Sets the columns info.
   *
   * @param array $columns
   *   Columns info.
   * @param array $headers_table_query
   *   Table headers info.
   */
  public function setColumns($columns = [], $headers_table_query = []) {
    $this->columns = $columns;
    $this->headers_table_query = $headers_table_query;
  }

  /**
   * Save audit log.
   *
   * @param string $event_type
   *   Event type.
   * @param string $description
   *   Audit log description.
   * @param string $details
   *   Audit log details.
   * @param string $company_name
   *   Company name.
   * @param string $company_nit
   *   Company document.
   * @param string $company_segment
   *   Company segment.
   */
  public function cardSaveAuditLog($event_type, $description, $details, $company_name = NULL, $company_nit = NULL, $company_segment = NULL) {
    // Save Audit log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();

    // Create array data[].
    $data = [
      'companyName' => $company_name,
      'companyDocument' => $company_nit,
      'companySegment' => $company_segment,
      'event_type' => $event_type,
      'description' => $description,
      'details' => t("Usuario @user_name $details", ['@user_name' => $name]),
    ];

    // Save audit log.
    $service_log->insertGenericLog($data);
  }

  /**
   * Get the value of the field passed as a parameter.
   *
   * @param string $field
   *   Field name to retrieve.
   *
   * @return mixed
   *   Field value.
   */
  public function getValue($field) {
    return $this->$field;
  }

  /**
   * Set the value of the field passed as a parameter.
   *
   * @param string $field
   *   Field name to set.
   * @param mixed $value
   *   Value to set the field.
   */
  public function setValue($field, $value) {
    $this->$field = $value;
  }

  /**
   * Sort the field property.
   *
   * @param string $field
   *   Field name.
   * @param string $key
   *   Key for getting the field value.
   */
  public function ordering($field, $key = '') {
    if ($key != '') {
      $this->$field = $this->configuration[$key][$field];
    }
    else {
      $this->$field = $this->configuration[$field];
    }

    // Ordering.
    if (!empty($this->$field)) {
      $this->cardSortArray($this->$field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return parent::build();
    // TODO: Implement build() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
