<?php

namespace Drupal\tbo_account\Plugin\Config;

use Drupal\tbo_account\Plugin\Block\ImportDataLogBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ImportDataLogBlockClass {

  protected $instance;

  protected $configuration;

  /**
   *
   */
  public function setConfig(ImportDataLogBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   *
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [
          'custom_id' => ['title' => t("Empresa"), 'service_field' => 'custom_id', 'show' => 1, 'weight' => 1, 'class' => '3-columns'],
          'status' => ['title' => t("Estado"), 'service_field' => 'status', 'show' => 1, 'weight' => 2, 'class' => '3-columns'],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'custom_id' => ['title' => t("Id empresa"), 'service_field' => 'custom_id', 'show' => 1, 'weight' => 1, 'class' => '2-columns', 'position' => 'left'],
          'status' => ['title' => t("Estado de importación"), 'service_field' => 'status_import', 'show' => 1, 'weight' => 2, 'class' => '2-columns', 'position' => 'left'],
          'details' => ['title' => t("Detalles de importación"), 'service_field' => 'description', 'show' => 1, 'weight' => 3, 'class' => '2-columns', 'position' => 'left'],
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'title' => [
            'title' => t('Titulo del bloque'),
            'label' => t('Logs de importación'),
            'show' => 1,
          ],
        ],
      ],
      'elements_page' => 10,
    ];
  }

  /**
   *
   */
  public function blockForm() {
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de la tabla'),
      '#open' => TRUE,
    ];
    $form['table_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Show'),
        t('Weight'),
        t('Espaciado'),
        t('Posición'),
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

    $table_fields = $this->configuration['table_options']['table_fields'];
    uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($table_fields as $id => $entity) {
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['table_options']['table_fields']['#weight'] = $entity['weight'];
      $form['table_options']['table_fields'][$id]['label'] = [
        '#plain_text' => $entity['title'],
      ];
      $form['table_options']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['table_options']['table_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      $form['table_options']['table_fields'][$id]['class'] = [
        '#type' => 'select',
        '#options' => [
          '' => t('Ninguno'),
          'destacado' => t('Destacado'),
          '1-columns' => t('Una columna'),
          '2-columns' => t('Dos columnas'),
          '3-columns' => t('Tres columnas'),
          '4-columns' => t('Cuatro columnas'),
        ],
        '#default_value' => $entity['class'],
      ];

      $form['table_options']['table_fields'][$id]['position'] = [
        '#type' => 'select',
        '#options' => [
          '' => t('Ninguno'),
          'left' => t('Izquierda'),
          'right' => t('Derecha'),
        ],
        '#default_value' => $entity['position'],
      ];

      $form['table_options']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    $form = $this->instance->cardBlockForm([], $form['table_options']);
    $form['elements_page'] = [
      '#type' => 'number',
      '#title' => t('Número de elementos por página'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['elements_page'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['table_options'] = $form_state->getValue(['table_options']);
    $this->configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $this->configuration['others_display'] = $form_state->getValue(['others_display']);
    $this->configuration['elements_page'] = $form_state->getValue('elements_page');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $this->instance->cardBuildHeader(FALSE, FALSE);

    $this->instance->setValue('directive', 'data-ng-import-data-log');
    $this->instance->setValue('config_name', 'importLogDataBlock');
    $this->instance->setValue('class', 'import-data-log');

    $status_filter_options = [
      'all' => t('Todos'),
      'fallo' => t('Fallo'),
      'exitoso' => t('Exitoso'),
      'error' => t('Error'),

    ];
    $this->instance->ordering('filters_fields', 'filters_options');
    $filters = [];
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        unset($classes);
        if ($key_filter == 'status') {
          $filters[$key_filter]['select_multiple'] = TRUE;
          $filters[$key_filter]['options'] = $status_filter_options;
        }
      }
      else {
        unset($filter[$key_filter]);
      }
    }
    $this->instance->setValue('filters', $filters);

    $this->instance->ordering('table_fields', 'table_options');
    $fields = [];
    $key_fields = [];
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $fields[$key_field]['key'] = $key_field;
        $fields[$key_field]['label'] = $field['title'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $fields[$key_field]['class'] = implode(" ", $classes);
        $fields[$key_field]['service_field'] = $field['service_field'];
        $fields[$key_field]['position'] = $field['position'];
        unset($classes);
        if (!in_array($field['service_field'], $key_fields)) {
          $key_fields[] = $field['service_field'];
        }
      }
      else {
        unset($field[$key_field]);
      }
    }
    $parameters = [
      'theme' => 'log_masive_enterprise',
      'library' => 'tbo_account/data-log',
    ];

    $others = [
      '#filters' => $filters,
      '#fields' => $fields,
      '#directive' => $this->instance->getValue('directive'),
      '#title' => $this->configuration['others_display']['table_fields']['title'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    $other_config = [
      'key_fields' => $key_fields,
      'config_pager' => $this->configuration['elements_page'],
    ];

    $config_block = $this->instance->cardBuildConfigBlock('/tbo_account/massive-import?_format=json', $other_config);
    $this->instance->cardBuildAddConfigDirective($config_block);

    // $this->instance->setValue('build', $build);.
    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
