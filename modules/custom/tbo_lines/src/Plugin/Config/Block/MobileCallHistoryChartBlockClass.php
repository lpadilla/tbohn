<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_lines\Plugin\Block\MobileCallHistoryChartBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'MobileCallHistoryChartBlock' block.
 */
class MobileCallHistoryChartBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * @param MobileCallHistoryChartBlock $instance
   * @param $config
   */
  public function setConfig(MobileCallHistoryChartBlock &$instance, &$config) {
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
          'start_date_voz_chart' => [
            'title' => t("Desde"),
            'label' => 'Desde',
            'service_field' => 'start_date_voz_chart',
            'show' => 1,
            'weight' => 1,
            'class' => '5-columns',
            'identifier' => 'start_date_voz_chart',
            'date_line' => 1,
          ],
          'end_date_voz_chart' => [
            'title' => t("Hasta"),
            'label' => 'Hasta',
            'service_field' => 'end_date_voz_chart',
            'show' => 1,
            'weight' => 2,
            'class' => '5-columns',
            'identifier' => 'end_date_voz_chart',
            'date_line' => 1,
          ],
        ],
      ],
      'min_table' => [
        'table_fields' => [
          'mtigo' => [
            'title' => t('Minutos a Tigo'),
            'type' => 'color',
            'color' => '#3764DB',
            'prefix' => 'mtigo',
            'show' => TRUE,
          ],
          'operador' => [
            'title' => t('Minutos a otros operadores'),
            'type' => 'color',
            'color' => '#a92ab8',
            'prefix' => 'operador',
            'show' => TRUE,
          ],
          'mdestino' => [
            'title' => t('Minutos a todo destino'),
            'type' => 'color',
            'color' => '#FBD767',
            'prefix' => 'mdestino',
            'show' => TRUE,
          ],
          'mdistancia' => [
            'title' => t('Minutos de Larga Distancia Inter.'),
            'type' => 'color',
            'color' => '#86B84A',
            'prefix' => 'mdistancia',
            'show' => TRUE,
          ],
          'mfavorito' => [
            'title' => t('Minutos favoritos Tigo'),
            'type' => 'color',
            'color' => '#ea9513',
            'prefix' => 'mfavorito',
            'show' => TRUE,
          ],
          'mroaming' => [
            'title' => t('Minutos Roaming'),
            'type' => 'color',
            'color' => '#ea2020',
            'prefix' => 'mroaming',
            'show' => TRUE,
          ],
        ],
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
      'title_tab' => [
        'table_fields' => [
          'title_saldo' => [
            'title' => t('Saldos de voz'),
            'label' => 'Saldos de voz',
            'service_field' => 'title_saldo',
            'show' => TRUE,
          ],
          'title_consumo' => [
            'title' => t('Consumos de voz'),
            'label' => 'Consumos de voz',
            'service_field' => 'title_consumo',
            'show' => TRUE,
          ],
          'tab_saldo' => [
            'title' => t('Tab Saldos de voz'),
            'label' => 'Saldos',
            'service_field' => 'tab_saldo',
            'show' => TRUE,
          ],
          'tab_consumo' => [
            'title' => t('Tab Consumos de voz'),
            'label' => 'Consumos',
            'service_field' => 'tab_consumo',
            'show' => TRUE,
          ],
        ],
      ],
      'redirec' => [
        'table_fields' => [
          'history_redirec' => [
            'title' => t('Enlace historial consumos'),
            'label' => t('Historial de consumos'),
            'url' => '/',
            'show' => TRUE,
          ],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'text_info' => [
            'title' => t('Texto informativo'),
            'label' => 'Los datos presentados son una referencia de consumo. Pueden variar dependiendo de la hora de generación del reporte.',
            'service_field' => 'text_info',
            'show' => TRUE,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();

    //table_options: fieldset que contiene todas las columnas de la tabla
    $form['min_table'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de saldo'),
      '#open' => TRUE,
    ];

    $form['min_table']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Title'), t('Color'), t('Show'),],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    $color = $this->configuration['min_table']['table_fields'];

    foreach ($color as $key => $value) {

      $form['min_table']['table_fields'][$key]['title'] = [
        '#plain_text' => $value['title'],
      ];

      $form['min_table']['table_fields'][$key]['color'] = [
        '#type' => $value['type'],
        '#default_value' => $value['color'],
      ];

      $form['min_table']['table_fields'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $value['show'],
      ];
    }

    $form['title_tab'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de títulos y tabs'),
      '#open' => TRUE,
    ];
    $form['title_tab']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Title'), t('Label'), t('Show')],
      '#empty' => t('There are no items yet. Add an item.'),
    ];
    $title_tab = $this->configuration['title_tab']['table_fields'];
    foreach ($title_tab as $id => $entity) {
      $form['title_tab']['table_fields'][$id]['title'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['title_tab']['table_fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#description' => '',
        '#default_value' => $entity['label'],
      ];
      $form['title_tab']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];
    }

    $form['redirec'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de redirección'),
      '#open' => TRUE,
    ];
    $form['redirec']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Title'), t('Label'), t('Url'), t('Show')],
      '#empty' => t('There are no items yet. Add an item.'),
    ];
    $title_tab = $this->configuration['redirec']['table_fields'];
    foreach ($title_tab as $id => $entity) {

      $form['redirec']['table_fields'][$id]['title'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['redirec']['table_fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#description' => '',
        '#default_value' => $entity['label'],
        '#size' => 30,
      ];
      $form['redirec']['table_fields'][$id]['url'] = [
        '#type' => 'textfield',
        '#description' => '',
        '#default_value' => $entity['url'],
        '#size' => 30,
      ];
      $form['redirec']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(MobileCallHistoryChartBlock &$instance, &$config) {

    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;
    $this->instance->cardBuildHeader(FALSE, FALSE);

    $this->instance->setValue('directive', 'data-ng-mobile-call-history-chart');
    $this->instance->setValue('config_name', 'mobileCallHistoryChartBlock');
    $this->instance->setValue('class', 'mobile-call-history-chart');
    //Ordering table_fields
    $this->instance->ordering('filters_fields', 'filters_options');

    $filters = [];
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {

        $filters_mobile[$key_filter."_m"]['identifier'] = $key_filter."_m";
        $filters_mobile[$key_filter."_m"]['label'] = $filter['label'];
        $classes_m = ["field-" . $filter['service_field']."_m", $filter['class']];
        $filters_mobile[$key_filter."_m"]['class'] = implode(" ", $classes_m);
        $filters_mobile[$key_filter."_m"]['service_field'] = $filter['service_field']."_m";
        $filters_mobile[$key_filter."_m"]['show'] = $filter['show'];
        $filters_mobile[$key_filter."_m"]['date_line'] = $filter['date_line'];

        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        $filters[$key_filter]['show'] = $filter['show'];
        $filters[$key_filter]['date_line'] = $filter['date_line'];
        if (!empty($filter['validate_length'])) {
          $filters[$key_filter]['validate_length'] = $filter['validate_length'];
          $filters_mobile[$key_filter."_m"]['validate_length'] = $filter['validate_length'];
        }
        if ($key_filter == 'user_role') {
          $filters[$key_filter]['select_multiple'] = TRUE;
          $filters_mobile[$key_filter."_m"]['select_multiple'] = TRUE;
        }
      }
    }

    //Set filters
    $this->instance->setValue('filters', $filters);
    $fil_conf = \Drupal::config('tbo_lines.consumptions_filters');
    $days = $fil_conf->get('days_query');
    $twig = \Drupal::service('twig');
    $text_info = t("El rango de fechas no debe ser superior a @days días", ['@days' => $days]);
    $twig->addGlobal('informative_text_filters_lines', $text_info);
    $table_other = $this->configuration['min_table']['table_fields'];
    $colorsChart = [
      $table_other['mtigo']['color'],
      $table_other['operador']['color'],
      $table_other['mfavorito']['color'],
      $table_other['mdistancia']['color'],
      $table_other['mdestino']['color'],
      $table_other['mroaming']['color'],
    ];
    $seriesChart = [
      $table_other['mtigo']['title'],
      $table_other['operador']['title'],
      $table_other['mfavorito']['title'],
      $table_other['mdistancia']['title'],
      $table_other['mdestino']['title'],
      $table_other['mroaming']['title'],
    ];
    if ($table_other['mtigo']['show'] == TRUE) {
      $field_view['mtigo'] = [
        'title' => $table_other['mtigo']['title'],
        'color' => $table_other['mtigo']['color'],
        'prefix' => $table_other['mtigo']['prefix'],
        'show' => $table_other['mtigo']['show'],
      ];
    }
    if ($table_other['operador']['show'] == TRUE) {
      $field_view['operador'] = [
        'title' => $table_other['operador']['title'],
        'color' => $table_other['operador']['color'],
        'prefix' => $table_other['operador']['prefix'],
        'show' => $table_other['operador']['show'],
      ];
    }
    if ($table_other['mfavorito']['show'] == TRUE) {
      $field_view['mfavorito'] = [
        'title' => $table_other['mfavorito']['title'],
        'color' => $table_other['mfavorito']['color'],
        'prefix' => $table_other['mfavorito']['prefix'],
        'show' => $table_other['mfavorito']['show'],
      ];
    }
    if ($table_other['mdistancia']['show'] == TRUE) {
      $field_view['mdistancia'] = [
        'title' => $table_other['mdistancia']['title'],
        'color' => $table_other['mdistancia']['color'],
        'prefix' => $table_other['mdistancia']['prefix'],
        'show' => $table_other['mdistancia']['show'],
      ];
    }
    if ($table_other['mdestino']['show'] == TRUE) {
      $field_view['mdestino'] = [
        'title' => $table_other['mdestino']['title'],
        'color' => $table_other['mdestino']['color'],
        'prefix' => $table_other['mdestino']['prefix'],
        'show' => $table_other['mdestino']['show'],
      ];
    }
    if ($table_other['mroaming']['show'] == TRUE) {
      $field_view['mroaming'] = [
        'title' => $table_other['mroaming']['title'],
        'color' => $table_other['mroaming']['color'],
        'prefix' => $table_other['mroaming']['prefix'],
        'show' => $table_other['mroaming']['show'],
      ];
    }
    $this->instance->cardSortArray($field_view);

    $parameters = [
      'library' => 'tbo_lines/card_mobile_call_history_chart',
      'theme' => 'card_mobile_call_history_chart',
    ];
    $title_tab = [
      'title_saldo' => ['show' => FALSE],
      'title_consumo' => ['show' => FALSE],
      'tab_saldo' => ['show' => FALSE],
      'tab_consumo' => ['show' => FALSE],
    ];
    $tab_config = $this->configuration['title_tab']['table_fields'];
    if ($tab_config['title_saldo']['show']) {
      $title_tab['title_saldo']['show'] = TRUE;
      $title_tab['title_saldo']['label'] = $tab_config['title_saldo']['label'];
    }
    if ($tab_config['title_consumo']['show']) {
      $title_tab['title_consumo']['show'] = TRUE;
      $title_tab['title_consumo']['label'] = $tab_config['title_consumo']['label'];
    }
    if ($tab_config['tab_saldo']['show']) {
      $title_tab['tab_saldo']['show'] = TRUE;
      $title_tab['tab_saldo']['label'] = $tab_config['tab_saldo']['label'];
    }
    if ($tab_config['tab_consumo']['show']) {
      $title_tab['tab_consumo']['show'] = TRUE;
      $title_tab['tab_consumo']['label'] = $tab_config['tab_consumo']['label'];
    }
    $env = (isset($_SESSION['serviceDetail']['serviceType'])) ? $_SESSION['serviceDetail']['serviceType'] : 'fijo';
    $show_card_enviroment = TRUE;
    if ($env == 'fijo') {
      $show_card_enviroment = FALSE;
    }
    $filter_date = \Drupal::service('tbo_lines.call_history_filter_date')->getFilterDate();
    $others = [
      '#fields' => $field_view,
      '#directive' => $this->instance->getValue('directive'),
      '#filters' => $this->instance->getValue('filters'),
      '#filters_mobile' => $filters_mobile,
      '#title' => $this->configuration['label'],
      '#show_title' => $this->configuration['label_display'],
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#title_tab' => $title_tab,
      '#redirec' => $this->configuration['redirec']['table_fields']['history_redirec'],
      '#text_info' => $this->configuration['others_display']['table_fields']['text_info'],
      '#title' => $this->configuration['label'],
      '#show_title' => $this->configuration['label_display'],
      '#show_card_enviroment' => $show_card_enviroment,
    ];
    $other_data = [
      'days_query' => $fil_conf->get('days_query'),
      'month_query' => $fil_conf->get('month_query'),
      'init_day' => $fil_conf->get('init_day'),
      'end_day' => $fil_conf->get('end_day'),
      'enviroment' => (isset($_SESSION['serviceDetail']['serviceType'])) ? $_SESSION['serviceDetail']['serviceType'] : 'fijo',
      'colorsChart' => $colorsChart,
      'seriesChart' => $seriesChart,
      'url_plan'=>'/tbo_lines/rest/mobile-call-history-plan?_format=json',
      'init_date' => $filter_date['init_date'],
      'end_date' => $filter_date['end_date']
    ];
    $this->instance->cardBuildVarBuild($parameters, $others);
    $config_block = $this->instance->cardBuildConfigBlock('/tbo_lines/rest/mobile-call-history-chart?_format=json', $other_data);
    $this->instance->cardBuildAddConfigDirective($config_block);
    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['min_table'] = $form_state->getValue('min_table');
    $this->configuration['filters_options'] = $form_state->getValue('filters_options');
    $this->configuration['buttons'] = $form_state->getValue('buttons');
    $this->configuration['title_tab'] = $form_state->getValue('title_tab');
    $this->configuration['redirec'] = $form_state->getValue('redirec');
    $this->configuration['others_display'] = $form_state->getValue('others_display');
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
