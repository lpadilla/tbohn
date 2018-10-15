<?php

namespace Drupal\tbo_permissions\Plugin\Config\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_permissions\Plugin\Block\ConsultCompaniesBlockedCardBlock;

/**
 * Provides a 'ConsultCompaniesBlockedCardBlockClass' block.
 *
 * @Block(
 *  id = "consult_companies_blocked_card_block",
 *  admin_label = @Translation("Consult Companies Blocked Card block"),
 * )
 */
class ConsultCompaniesBlockedCardBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function setConfig(ConsultCompaniesBlockedCardBlock &$instance, &$config) {
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
          'document_number' => [
            'title' => t('Número de documento'),
            'label' => 'Número de documento',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 3,
            'class' => '3-columns',
            'input_type' => 'text',
          ],
          'document_type' => [
            'title' => t('Tipo de documento'),
            'label' => t('Tipo de documento'),
            'service_field' => 'document_type',
            'show' => 1,
            'weight' => 2,
            'class' => '3-columns',
            'input_type' => 'text',
            'type' => 'selectable',
            'none' => t('Todos'),
          ],
          'card_name' => [
            'title' => t('Nombre del card'),
            'label' => 'Nombre del card',
            'service_field' => 'card_name',
            'show' => 1,
            'weight' => 1,
            'class' => '6-columns',
            'validate_length' => 200,
            'autocomplete' => TRUE,
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'document_type' => [
            'title' => t('Tipo de Documento'),
            'label' => t('Tipo de Documento'),
            'service_field' => 'document_type',
            'show' => 1,
            'weight' => 1,
          ],
          'document_number' => [
            'title' => t('Número de Documento'),
            'label' => t('Número de Documento'),
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 2,
          ],
          'company_name' => [
            'title' => t('Nombre de la Empresa'),
            'label' => t('Nombre de la Empresa'),
            'service_field' => 'company_name',
            'show' => 1,
            'weight' => 3,
          ],
          'block_event_date' => [
            'title' => t('Fecha de Bloqueo del Card'),
            'label' => t('Fecha de Bloqueo del Card'),
            'service_field' => 'block_event_date',
            'show' => 1,
            'weight' => 4,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1,
      ],
      'buttons' => [
        'table_fields' => [
          'search_consult_companies_blocked_card' => [
            'title' => t('Botón Consultar'),
            'service_field' => 'action_card_search_companies_blocked_card',
            'label' => t('Consultar'),
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
          ],
          'clear_filters_consult_companies_blocked_card' => [
            'title' => t('Botón Limpiar'),
            'label' => t('Limpiar'),
            'service_field' => 'action_card_clear_companies_blocked_card',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = $this->instance->cardBlockForm();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(TRUE, FALSE);

    $this->instance->setValue('directive', 'data-ng-consult-companies-blocked-card');
    $this->instance->setValue('config_name', 'consultCompaniesBlockedCardBlock');
    $this->instance->setValue('class', 'block-consultCompaniesBlockedCardBlock');

    // ---------------
    // TABLE FIELDS.
    // ---------------
    // Si se construye logica propia para las columnas, se debe utilizar
    // el metodo $this->setColumns($columns, $headers_table_query)
    $columns = [];
    $headers_table_query = [];

    // Ordering table_fields.
    $this->instance->ordering('table_fields', 'table_options');

    // Recorremos $table_fields para verificar las columnas a mostrar.
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $columns[$key_field]['key'] = $key_field;
        $columns[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $columns[$key_field]['class'] = implode(" ", $classes);
        $columns[$key_field]['service_field'] = $field['service_field'];

        $headers_table_query[$key_field]['value'] = $key_field;
        $headers_table_query[$key_field]['type'] = 'user';

        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    // Set columns and headers_table_query.
    $this->instance->setColumns($columns, $headers_table_query);

    // Si se construye una lógica propia para los filtros,
    // se debe setear el valor de los filtros con $this->setFilters($filters)
    // ---------------
    // FILTERS FIELDS.
    // ---------------
    // Ordering table_fields.
    $this->instance->ordering('filters_fields', 'filters_options');

    // Load DocumentTypes.
    $entitiesService = \Drupal::service('tbo_entities.entities_service');
    $documentTypesInfo = $entitiesService->getDocumentTypes();
    $documentTypes = [];
    foreach ($documentTypesInfo as $key => $value) {
      $documentTypes[$value['id']] = $value['label'];
    }

    // Set filters configurations.
    $filters = [];
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['label'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        $filters[$key_filter]['type'] = isset($filter['type']) ? $filter['type'] : '';
        $filters[$key_filter]['input_type'] = $filter['input_type'];

        if (!empty($filter['validate_length'])) {
          $filters[$key_filter]['validate_length'] = $filter['validate_length'];
        }

        $filters[$key_filter]['validations'] = $filter['validations'];

        if (isset($filter['disabled'])) {
          $filters[$key_filter]['disabled'] = $filter['disabled'];
        }

        if (isset($filter['none'])) {
          $filters[$key_filter]['none'] = $filter['none'];
        }

        if (isset($filter['type']) && ($filter['type'] == 'selectable_list' || $filter['type'] == 'selectable')) {
          if ($key_filter == 'document_type') {
            $filters[$key_filter]['options'] = $documentTypes;
          }
        }
      }
    }

    // Set filters.
    $this->instance->setValue('filters', $filters);

    // Set session var.
    $this->instance->cardBuildSession(TRUE, [], 'tbo_permissions');

    // Construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'consult_companies_blocked_card',
      'library' => 'tbo_permissions/consult-companies-blocked-card',
      'columns' => $columns,
    ];

    // Set title.
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    // Parameter additional.
    $others = [
      '#title' => $title,
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular,
    // se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-permissions/rest/consult-companies-blocked-card?_format=json');

    // Se agrega la configuración necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block);

    // Se guarda el log de auditoria.
    $this->instance->cardSaveAuditLog('Cuenta', 'Super Admin accede a activacion de cards para empresas', 'consulta activación de cards para empresas');

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

    if (in_array('super_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
