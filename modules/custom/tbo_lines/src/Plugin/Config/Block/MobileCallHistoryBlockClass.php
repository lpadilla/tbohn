<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_lines\Plugin\Block\MobileCallHistoryBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'MobileCallHistoryBlock' block.
 */
class MobileCallHistoryBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * @param MobileCallHistoryBlock $instance
   * @param $config
   */
  public function setConfig(MobileCallHistoryBlock &$instance, &$config) {
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
          'start_date_voz' => [
            'title' => t("Desde"),
            'label' => 'Desde',
            'service_field' => 'start_date_voz',
            'show' => 1,
            'weight' => 1,
            'class' => '5-columns',
            'identifier' => 'start_date_voz',
            'date_line' => 1,
          ],
          'end_date_voz' => [
            'title' => t("Hasta"),
            'label' => 'Hasta',
            'service_field' => 'end_date_voz',
            'show' => 1,
            'weight' => 2,
            'class' => '5-columns',
            'identifier' => 'end_date_voz',
            'date_line' => 1,
          ],
        ],
      ],
      'table_movil' => [
        'table_fields' => [
          'date_hour' => [
            'title' => t('Fecha y hora'),
            'label' => 'Fecha y hora',
            'service_field' => 'date_hour',
            'weight' => 2,
            'show' => TRUE,
            'class' => '6-columns',
          ],
          'number' => [
            'title' => t('Destino'),
            'label' => 'Destino',
            'service_field' => 'number',
            'weight' => 3,
            'show' => TRUE,
            'class' => '4-columns',
          ],
          'time_call' => [
            'title' => t('Duración'),
            'label' => 'Duración',
            'service_field' => 'time_call',
            'weight' => 3,
            'show' => TRUE,
            'class' => '4-columns',
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'date' => [
            'title' => t('Fecha'),
            'label' => 'Fecha',
            'service_field' => 'date',
            'weight' => 1,
            'show' => TRUE,
            'class' => '4-columns',
          ],
          'hour' => [
            'title' => t('Hora'),
            'label' => 'Hora',
            'service_field' => 'hour',
            'weight' => 2,
            'show' => TRUE,
            'class' => '4-columns',
          ],
          'number' => [
            'title' => t('Destino'),
            'label' => 'Destino',
            'service_field' => 'number',
            'weight' => 3,
            'show' => TRUE,
            'class' => '4-columns',
          ],
          'time_call' => [
            'title' => t('Duración'),
            'label' => 'Duración',
            'service_field' => 'time_call',
            'weight' => 3,
            'show' => TRUE,
            'class' => '4-columns',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [
            'number_rows_pages' => 10,
          ],
          'show_margin' => [
            'show_margin_card' => FALSE,
          ],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'text_info' => [
            'title' => t('Texto informativo'),
            'label' => 'Los datos presentados son una referencia de consumo. Pueden variar dependiendo de la hora de generación del reporte',
            'service_field' => 'text_info',
            'show' => TRUE,
          ],
          'download_button' => [
            'title' => t('botón descargar'),
            'label' => 'Descargar reporte',
            'service_field' => 'download_button',
            'show' => TRUE,
          ],
        ],
        'show_report' => TRUE,
      ],
      'buttons' => [
        'table_fields' => [
          'view_button' => [
            'title' => t('Botón "ver"  de filtros'),
            'label' => 'Ver',
            'service_field' => 'view_button',
            'show' => TRUE,
          ],
        ],
      ],
      'no_data' => 'No hay  información disponible para  las  fechas seleccionadas. Por favor intenta con un rango de fechas diferentes.',
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();
    $form['others_display']['table_fields']['#header'] = [
      t('Title'),
      t('Label'),
      t('Show'),
    ];
    $form['others_display']['show_report'] = [
      '#type' => 'checkbox',
      '#title' => t('Mostrar reporte de consumo de voz'),
      '#default_value' => $this->configuration['others_display']['show_report'],
    ];
    $form['table_movil'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones tabla vista móvil'),
      '#open' => TRUE,
    ];
    $form['table_movil']['table_fields'] = [
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
    $fields_movil = $this->configuration['table_movil']['table_fields'];
    uasort($fields_movil, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    foreach ($fields_movil as $id => $entity) {
      $form['table_movil']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['table_movil']['table_fields']['#weight'] = $entity['weight'];
      $form['table_movil']['table_fields'][$id]['title'] = [
        '#plain_text' => $entity['title'],
      ];
      if (isset($entity['label'])) {
        $form['table_movil']['table_fields'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        ];
      }
      else {
        $form['table_movil']['table_fields'][$id]['label'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['table_movil']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['table_movil']['table_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      if (isset($entity['class'])) {
        $form['table_movil']['table_fields'][$id]['class'] = [
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
        $form['table_movil']['table_fields'][$id]['class'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['table_movil']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }
    $form['no_data'] = [
      '#type' => 'textfield',
      '#title' => t('Mensaje datos vacíos'),
      '#default_value' => $this->configuration['no_data'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(MobileCallHistoryBlock &$instance, &$config) {

    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;
    $this->instance->cardBuildHeader(FALSE, TRUE);

    $this->instance->setValue('directive', 'data-ng-mobile-call-history');
    $this->instance->setValue('config_name', 'mobileCallHistoryBlock');
    $this->instance->setValue('class', 'mobile-call-history');
    //Ordering table_fields
    $this->instance->ordering('filters_fields', 'filters_options');

    $filters = [];

    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {

        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        $filters[$key_filter]['show'] = $filter['show'];
        $filters[$key_filter]['date_line'] = $filter['date_line'];

        if (!empty($filter['validate_length'])) {
          $filters[$key_filter]['validate_length'] = $filter['validate_length'];
        }
        if ($key_filter == 'user_role') {
          $filters[$key_filter]['select_multiple'] = TRUE;
        }
      }
    }

    $this->instance->setValue('filters', $filters);
    $fil_conf = \Drupal::config('tbo_lines.consumptions_filters');
    $days = $fil_conf->get('days_query');
    $twig = \Drupal::service('twig');
    $text_info = t("El rango de fechas no debe ser superior a @days días", ['@days'=> $days]);
    $twig->addGlobal('informative_text_filters_lines', $text_info);
    $table_movil = $this->configuration['table_movil']['table_fields'];
    if ($table_movil['date_hour']['show'] == TRUE) {
      $fields_movil['date_hour'] = [
        'show' => $table_movil['date_hour']['show'],
        'label' => $table_movil['date_hour']['label'],
        'class' => $table_movil['date_hour']['class'],
        'service_field' => $table_movil['date_hour']['service_field'],
        'weight' => $table_movil['date_hour']['weight'],
      ];
    }
    if ($table_movil['time_call']['show'] == TRUE) {
      $fields_movil['time_call'] = [
        'show' => $table_movil['time_call']['show'],
        'label' => $table_movil['time_call']['label'],
        'class' => $table_movil['time_call']['class'],
        'service_field' => $table_movil['time_call']['service_field'],
        'weight' => $table_movil['time_call']['weight'],
      ];
    }
    if ($table_movil['number']['show'] == TRUE) {
      $fields_movil['number'] = [
        'show' => $table_movil['number']['show'],
        'label' => $table_movil['number']['label'],
        'class' => $table_movil['number']['class'],
        'service_field' => $table_movil['number']['service_field'],
        'weight' => $table_movil['number']['weight'],
      ];
    }
    $this->instance->cardSortArray($fields_movile);
    $parameters = [
      'library' => 'tbo_lines/card_mobile_call_history',
      'theme' => 'card_mobile_call_history',
    ];
    $others = [
      '#directive' => $this->instance->getValue('directive'),
      '#report' => $this->configuration['others_display']['table_fields'],
      '#show_report' => $this->configuration['others_display']['show_report'],
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#title' => $this->configuration['label'],
      '#show_title' => $this->configuration['label_display'],
      '#filters' => $this->instance->getValue('filters'),
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#fields_movil' => $fields_movil
    ];
    $filter_date = \Drupal::service('tbo_lines.call_history_filter_date')->getFilterDate();

    $this->instance->cardBuildVarBuild($parameters, $others);
    $other_data = [
      'days_query' => $fil_conf->get('days_query'),
      'month_query' => $fil_conf->get('month_query'),
      'init_day' => $fil_conf->get('init_day'),
      'end_day' => $fil_conf->get('end_day'),
      'empty_message' => $this->configuration['no_data'],
      'init_date' => $filter_date['init_date'],
      'end_date' => $filter_date['end_date']
    ];
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-lines/mobile-call-history?_format=json', $other_data);
    $this->instance->cardBuildAddConfigDirective($config_block);

    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['filters_options'] = $form_state->getValue('filters_options');
    $this->configuration['table_options'] = $form_state->getValue('table_options');
    $this->configuration['table_movil'] = $form_state->getValue('table_movil');
    $this->configuration['others_display'] = $form_state->getValue('others_display');
    $this->configuration['others'] = $form_state->getValue('others');
    $this->configuration['elements_page'] = $form_state->getValue('elements_page');
    $this->configuration['buttons'] = $form_state->getValue('buttons');
    $this->configuration['no_data'] = $form_state->getValue('no_data');
    $this->configuration['info_text'] = $form_state->getValue('info_text');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $roles = $account->getRoles();
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }
}
