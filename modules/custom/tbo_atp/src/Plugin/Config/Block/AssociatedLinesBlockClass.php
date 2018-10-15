<?php

namespace Drupal\tbo_atp\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_atp\Plugin\Block\AssociatedLinesBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'AssociatedLinesBlockClass' block.
 */
class AssociatedLinesBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Set instance configuration block.
   *
   * @param \Drupal\tbo_atp\Plugin\Block\AssociatedLinesBlock $instance
   *   Instance AssociatedLinesBlock block.
   * @param array $config
   *   Instance config block.
   */
  public function setConfig(AssociatedLinesBlock &$instance, array &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [
          'client_name' => [
            'title' => 'Nombre del cliente',
            'service_field' => 'client_name',
            'show' => 1,
            'weight' => 1,
          ],
          'title' => [
            'title' => 'Titulo',
            'label' => 'Lineas asociadas',
            'service_field' => 'title',
            'show' => 1,
            'weight' => 2,
            'class' => '12-columns',
          ],
          'descriptive_text' => [
            'title' => 'Texto descriptivo',
            'label' => 'Realice consulta de los paquetes que tiene cada área de su empresa, los consumos y las líneas asociadas a cada perfil',
            'service_field' => 'descriptive_text',
            'show' => 1,
            'weight' => 3,
            'class' => '12-columns',
          ],
          'section_of_associated_lines' => [
            'title' => 'Seccion de lineas asociadas',
            'service_field' => 'section_of_associated_lines',
            'show' => 1,
            'weight' => 4,
          ],
          'msisdn_line' => [
            'title' => t("Seccion de lineas asociadas - Linea movil"),
            'service_field' => 'msisdn_line',
            'show' => 1,
            'weight' => 5,
          ],
          'type_line' => [
            'title' => t("Seccion de lineas asociadas - Tipo de plan"),
            'service_field' => 'type_line',
            'show' => 1,
            'weight' => 6,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'download' => [
            'title' => t('Botón Descargar'),
            'label' => t("Descargar"),
            'service_field' => 'detail',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
            'type_report' => 'csv',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [
            'number_rows_pages' => 10,
          ],
          'show_margin' => [
            'show_margin_card' => 1,
          ],
          'labels_download' => [
            'nit' => 'NIT',
            'line_number' => 'Número de línea',
            'plan_type' => 'Tipo de plan',
            'profile_name' => 'Nombre del perfil',
            'profile_description' => 'Descripción del perfil',
            'father_account' => 'Cuenta padre',
            'sponsor_account' => 'Cuenta sponsor',
            'category_name' => 'Nombre del servicio o categoría',
            'category_package' => 'Paquetes de servicios o recursos del plan',
            'category_value' => 'Valor total de paquete de servicios',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $labels_download = $this->configuration['others']['config']['labels_download'];
    $fields['others']['labels_download'] = [
      '#type' => 'details',
      '#title' => t('Nombres de los campos para exportar'),
      '#open' => TRUE,
    ];

    $fields['others']['labels_download']['nit'] = [
      '#type' => 'textfield',
      '#title' => 'Nit',
      '#default_value' => $labels_download['nit'],
    ];

    $fields['others']['labels_download']['line_number'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['line_number'],
    ];

    $fields['others']['labels_download']['plan_type'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['plan_type'],
    ];

    $fields['others']['labels_download']['profile_name'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['profile_name'],
    ];

    $fields['others']['labels_download']['profile_description'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['profile_description'],
    ];

    $fields['others']['labels_download']['father_account'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['father_account'],
    ];

    $fields['others']['labels_download']['sponsor_account'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['sponsor_account'],
    ];

    $fields['others']['labels_download']['category_name'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['category_name'],
    ];

    $fields['others']['labels_download']['category_package'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['category_package'],
    ];

    $fields['others']['labels_download']['category_value'] = [
      '#type' => 'textfield',
      '#title' => 'Número de línea',
      '#default_value' => $labels_download['category_value'],
    ];

    $form = $this->instance->cardBlockForm($fields['others']);

    // Table_fields: Config  fields table.
    $table_fields = $this->configuration['table_options']['table_fields'];

    if (empty($table_options)) {
      // Table_options: fieldset que contiene todas las columnas de la tabla.
      $form['table_options'] = [
        '#type' => 'details',
        '#title' => t('Configuraciones de los campos'),
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
      }

      // Se ordenan los filtros segun lo establecido en la configuración.
      uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      // Se crean todas las columnas de la tabla que mostrara la información.
      foreach ($table_fields as $id => $entity) {
        if ($id != 'title' && $id != 'descriptive_text' && $id != 'section_of_associated_lines' && $id != 'client_name') {
          // TableDrag: Mark the table row as draggable.
          $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
        }

        // TableDrag:
        // Sort the table row according to its existing/configured weight.
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
              '' => t('Ninguno'),
              'destacado' => t('Destacado'),
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

        $form['buttons']['table_fields'][$id]['type_report'] = [
          '#type' => 'select',
          '#options' => [
            'csv' => t('csv'),
            'xlsx' => t('excel'),
            'txt' => t('texto'),
          ],
          '#default_value' => $entity['type_report'],
        ];

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

    // Change title paginate.
    $form['others']['config']['paginate']['number_rows_pages']['#title'] = t('Numero de elementos a mostrar por columna');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(AssociatedLinesBlock &$instance, $configuration) {
    // Set data uuid, generate filters_fields, generate table_fields.
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'associatedLinesBlock');
    $instance->setValue('directive', 'data-ng-associated-lines');
    $instance->setValue('class', 'associated-lines-block');

    // Ordering table_fields.
    $instance->ordering('table_fields', 'table_options');
    $data = [];
    foreach ($instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $data[$key_field]['key'] = $key_field;
        $data[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $data[$key_field]['class'] = implode(" ", $classes);
        $data[$key_field]['service_field'] = $field['service_field'];
        $data[$key_field]['position'] = $field['position'];
        unset($classes);

        if ($key_field == 'msisdn_line' || $key_field == 'type_line') {
          $data['fields_table'][] = $data[$key_field];
          unset($data[$key_field]);
        }
      }
      else {
        unset($field[$key_field]);
      }
    }

    $instance->setValue('columns', $data);

    // Building var $build.
    $parameters = [
      'theme' => 'associated_lines',
      'library' => 'tbo_atp/associated-lines',
    ];

    // Set title.
    $title = FALSE;
    if ($configuration['label_display'] == 'visible') {
      $title = $configuration['label'];
    }

    // Set as dynamic plan's type.
    $plan_type = [
      'PRE' => t('ATP AssociatedLines - PRE'),
      'ATP' => t('ATP AssociatedLines - ATP'),
      'POS' => t('ATP AssociatedLines - POS'),
      'CON' => t('ATP AssociatedLines - CON'),
      'RTB' => t('ATP AssociatedLines - RTB'),
    ];

    $others = [
      '#buttons' => $configuration['buttons']['table_fields'],
      '#title' => $title,
      '#margin' => $configuration['others']['config']['show_margin'],
      '#plan_type' => $plan_type,
    ];

    $instance->cardBuildVarBuild($parameters, $others);

    // Add other_config directive.
    $other_config = [];

    // Set config_block.
    $config_block = $instance->cardBuildConfigBlock('/tbo-atp/associated-lines?_format=json', $other_config);

    // Add configuration drupal.js object.
    $instance->cardBuildAddConfigDirective($config_block, 'associatedLinesBlock');

    // Set session config labels export.
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_atp');
    $labels_export = $configuration['others']['config']['labels_download'];
    $tempStore->set('tbo_atp_labels_' . $instance->getValue('uuid'), $labels_export);

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

    if ((in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) && ($_SESSION['company']['environment'] == 'movil' || $_SESSION['company']['environment'] == 'both')) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
