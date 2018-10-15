<?php

namespace Drupal\tbo_atp\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_atp\Plugin\Block\SearchByProfileBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'SearchByProfileBlockClass' block.
 */
class SearchByProfileBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Set instance configuration block.
   *
   * @param \Drupal\tbo_atp\Plugin\Block\SearchByProfileBlock $instance
   *   Instance SearchByProfileBlock block.
   * @param array $config
   *   Instance config block.
   */
  public function setConfig(SearchByProfileBlock &$instance, array &$config) {
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
          'image' => [
            'title' => t("Imagen"),
            'service_field' => 'image',
            'show' => 1,
            'weight' => 1,
          ],
        ],
        'left' => [
          'table_fields' => [
            'client_name' => [
              'title' => 'Nombre del cliente',
              'service_field' => 'client_name',
              'show' => 1,
              'weight' => 1,
            ],
            'image' => [
              'title' => t("Imagen"),
              'service_field' => 'image',
              'show' => 1,
              'weight' => 2,
            ],
            'linesAmount_profile' => [
              'title' => t("Lineas asociadas"),
              'label' => 'Lineas',
              'service_field' => 'linesAmount_profile',
              'show' => 1,
              'weight' => 3,
              'position' => 'left',
              'class' => '12-columns',
            ],
            'name_profile' => [
              'title' => t("Perfil"),
              'service_field' => 'name_profile',
              'show' => 1,
              'weight' => 4,
              'position' => 'left',
              'class' => '12-columns',
            ],
            'billingAccount_profile' => [
              'title' => t("Contrato"),
              'label' => 'Contrato',
              'service_field' => 'billingAccount_profile',
              'show' => 1,
              'weight' => 5,
              'position' => 'left',
              'class' => '12-columns',
              'class_mobile' => '4-columns',
            ],
          ],
        ],
        'right' => [
          'table_fields' => [
            'value_profile' => [
              'title' => t("Valor"),
              'label' => 'Valor',
              'service_field' => 'value_profile',
              'show' => 1,
              'weight' => 1,
              'position' => 'right',
              'class' => '6-columns',
              'class_mobile' => '4-columns',
            ],
            'totalValue_profile' => [
              'title' => t("Valor total"),
              'label' => 'Valor total',
              'service_field' => 'totalValue_profile',
              'show' => 1,
              'weight' => 2,
              'position' => 'right',
              'class' => '6-columns',
              'class_mobile' => '4-columns',
            ],
          ],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'serviceCollection_profile' => [
            'title' => t("Recursos"),
            'label' => 'Recursos',
            'service_field' => 'serviceCollection_profile',
            'show' => 1,
            'weight' => 1,
            'class' => '6-columns',
          ],
          'description_profile' => [
            'title' => t("Descripción"),
            'label' => 'Descripción',
            'service_field' => 'description_profile',
            'show' => 1,
            'weight' => 2,
            'class' => '6-columns',
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'detail' => [
            'title' => t('Botón ver detalles'),
            'label' => 'VER DETALLES',
            'service_field' => 'detail',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
          'show_lines' => [
            'title' => t('Botón ver lineas'),
            'label' => 'VER LINEAS',
            'url' => t('/lineas-asociadas'),
            'url_description' => t('Ejemplo consulta-por-lineas-asociadas o https://www.tigo.com.co/'),
            'service_field' => 'action_card',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'scroll' => '10',
          'show_margin' => [
            'show_margin_card' => 1,
            'show_margin_internal_card' => 1,
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

    $form = $this->instance->cardBlockForm($field);

    // Table_options: fieldset que contiene todas las columnas de la tabla.
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de los campos'),
      '#open' => TRUE,
    ];

    $positions = [
      'left' => 'izquierda',
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
          t('Espaciado Mobile'),
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

        if (isset($entity['class_mobile'])) {
          $form['table_options'][$key]['table_fields'][$id]['class_mobile'] = [
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
            '#default_value' => $entity['class_mobile'],
          ];
        }
        else {
          $form['table_options'][$key]['table_fields'][$id]['class_mobile'] = [
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

    $form['others_display'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones del detalle del perfil'),
      '#open' => TRUE,
    ];

    $form['others_display']['table_fields'] = [
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

    $data_fields_details = $this->configuration['others_display']['table_fields'];
    // Se ordenan los filtros segun lo establecido en la configuración.
    uasort($data_fields_details, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    // Se crean todas las columnas de la tabla que mostrara la información.
    foreach ($data_fields_details as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['others_display']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag:
      // Sort the table row according to its existing/configured weight.
      $form['others_display']['table_fields']['#weight'] = $entity['weight'];
      // Some table columns containing raw markup.
      $form['others_display']['table_fields'][$id]['title'] = [
        '#plain_text' => $entity['title'],
      ];

      // Some table columns containing raw markup.
      if (isset($entity['label'])) {
        $form['others_display']['table_fields'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        ];
      }
      else {
        $form['others_display']['table_fields'][$id]['label'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['others_display']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // TableDrag: Weight column element.
      $form['others_display']['table_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      if (isset($entity['class'])) {
        $form['others_display']['table_fields'][$id]['class'] = [
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
        $form['others_display']['table_fields'][$id]['class'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['others_display']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(SearchByProfileBlock &$instance, $configuration) {
    // Set data uuid, generate filters_fields, generate table_fields.
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'searchByProfileBlock');
    $instance->setValue('directive', 'data-ng-search-by-profile');
    $instance->setValue('class', 'block-search-by-profile');

    // Build columns table.
    // Ordering table_fields_left.
    $data_fields_left = $configuration['table_options']['left']['table_fields'];
    uasort($data_fields_left, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $data = $data_mobile = [];
    foreach ($data_fields_left as $key_field => $field) {
      if ($field['show'] == 1) {
        $data[$key_field]['key'] = $key_field;
        $data[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $data[$key_field]['class'] = implode(" ", $classes);
        $data[$key_field]['class_field'] = $field['class'];
        $data[$key_field]['service_field'] = $field['service_field'];
        $data[$key_field]['position'] = $field['position'];
        unset($classes);
        // Add class mobile.
        if ($field['class_mobile']) {
          $data_mobile[$key_field]['key'] = $key_field;
          $data_mobile[$key_field]['label'] = $field['label'];
          $classes = ["field-" . $field['service_field']];
          $data_mobile[$key_field]['class'] = implode(" ", $classes);
          $data_mobile[$key_field]['class_field'] = $field['class'];
          $data_mobile[$key_field]['service_field'] = $field['service_field'];
          $data_mobile[$key_field]['position'] = $field['position'];
          $data_mobile[$key_field]['class_mobile'] = $field['class_mobile'];
        }
      }
      else {
        unset($field[$key_field]);
      }
    }
    // Set columns.
    $instance->setValue('columns', $data);

    // Ordering table_fields_right.
    $data_fields_right = $configuration['table_options']['right']['table_fields'];
    uasort($data_fields_right, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    foreach ($data_fields_right as $key_field => $field) {
      if ($field['show'] == 1) {
        $data_mobile[$key_field]['key'] = $key_field;
        $data_mobile[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $data_mobile[$key_field]['class'] = implode(" ", $classes);
        $data_mobile[$key_field]['class_field'] = $field['class'];
        $data_mobile[$key_field]['service_field'] = $field['service_field'];
        $data_mobile[$key_field]['position'] = $field['position'];
        unset($classes);

        // Add class mobile.
        $data_mobile[$key_field]['class_mobile'] = $field['class_mobile'];
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Build columns details.
    $details_profile = $configuration['others_display']['table_fields'];
    uasort($details_profile, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $data = [];
    foreach ($details_profile as $key_field => $field) {
      if ($field['show'] == 1) {
        $data[$key_field]['key'] = $key_field;
        $data[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field']];
        $data[$key_field]['class'] = implode(" ", $classes);
        $data[$key_field]['class_field'] = $field['class'];
        $data[$key_field]['service_field'] = $field['service_field'];
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Building var $build.
    $parameters = [
      'theme' => 'search_by_profile',
      'library' => 'tbo_atp/search-by-profile',
    ];

    // Set title.
    $title = FALSE;
    if ($configuration['label_display'] == 'visible') {
      $title = $configuration['label'];
    }

    // Set as dynamic category.
    $category = [
      'ACCOUNT' => t('ATP ProfileCategory - ACCOUNT'),
      'CUG' => t('ATP ProfileCategory - CUG'),
      'DATA' => t('ATP ProfileCategory - DATA'),
      'LDI' => t('ATP ProfileCategory - LDI'),
      'SMS' => t('ATP ProfileCategory - SMS'),
      'SMSON' => t('ATP ProfileCategory - SMSON'),
      'SMSTD' => t('ATP ProfileCategory - SMSTD'),
      'VOZON' => t('ATP ProfileCategory - VOZON'),
      'VOZTD' => t('ATP ProfileCategory - VOZTD'),
    ];

    $others = [
      '#buttons' => $configuration['buttons']['table_fields'],
      '#title' => $title,
      '#margin' => $configuration['others']['config']['show_margin'],
      '#columns_rigth' => $data_mobile,
      '#columns_details' => $data,
      '#categories' => $category,
    ];

    $instance->cardBuildVarBuild($parameters, $others);

    // Add other_config directive.
    $other_config = [
      'scroll' => $configuration['others']['config']['scroll'],
      'text_btn_detail_normal' => $configuration['buttons']['table_fields']['detail']['label'],
      'text_btn_detail_expanded' => t('VER MENOS'),
    ];

    // Set config_block.
    $config_block = $instance->cardBuildConfigBlock('/tbo-atp/search-by-profile?_format=json', $other_config);

    // Add configuration drupal.js object.
    $instance->cardBuildAddConfigDirective($config_block, 'searchByProfileBlock');

    // Get current path.
    $path = \Drupal::request()->getPathInfo();
    // Set session config labels export.
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_atp');
    $tempStore->set('tbo_atp_search_by_profile_temp_path', $path);

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

    /*if ((in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) && ($_SESSION['company']['environment'] == 'movil' || $_SESSION['company']['environment'] == 'both')) {
      \Drupal::logger('atp_serach')->info('acces true');
      return AccessResult::allowed();
    }*/

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
