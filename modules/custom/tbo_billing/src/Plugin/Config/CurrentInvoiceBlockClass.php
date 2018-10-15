<?php

namespace Drupal\tbo_billing\Plugin\Config;

use Drupal\tbo_billing\Plugin\Block\CurrentInvoiceBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'CurrentInvoiceBlock' block.
 */
class CurrentInvoiceBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * @param \Drupal\tbo_billing\Plugin\Block\CurrentInvoiceBlock $instance
   * @param $config
   */
  public function setConfig(CurrentInvoiceBlock &$instance, &$config) {
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
          'address' => [
            'title' => t("Dirección"),
            'service_field' => 'address',
            'show' => 1,
            'weight' => 1,
            'class' => '2-columns',
            'max_length' => 200,
          ],
          'reference' => [
            'title' => t("Búsqueda"),
            'service_field' => 'reference',
            'show' => 1,
            'weight' => 2,
            'class' => '2-columns',
            'max_length' => 200,
          ],
          'contract' => [
            'title' => t("Contrato"),
            'service_field' => 'contract',
            'show' => 1,
            'weight' => 3,
            'class' => '2-columns',
            'max_length' => 200,
          ],
          'invoices' => [
            'title' => t("Facturas"),
            'service_field' => 'invoice',
            'show' => 1,
            'weight' => 3,
            'class' => '2-columns',
          ],
          'order' => [
            'title' => t("Ordenar"),
            'service_field' => 'order',
            'show' => 1,
            'weight' => 3,
            'class' => '2-columns',
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'address' => [
            'title' => t("Dirección"),
            'service_field' => 'address',
            'show' => 1,
            'position' => 'left',
            'weight' => 1,
            'class' => '2-columns',
          ],
          'invoice_value' => [
            'title' => t("Valor de la factura"),
            'service_field' => 'invoice_value',
            'show' => 1,
            'position' => 'left',
            'weight' => 2,
            'class' => '2-columns',
          ],
          'date_payment' => [
            'title' => t("Fecha de pago"),
            'service_field' => 'date_payment',
            'show' => 1,
            'position' => 'left',
            'weight' => 3,
            'class' => '2-columns',
          ],
          'contract' => [
            'title' => t("Contrato"),
            'service_field' => 'contract',
            'show' => 1,
            'position' => 'right',
            'weight' => 3,
            'class' => '2-columns',
          ],
          'payment_reference' => [
            'title' => t("Referencia de pago"),
            'service_field' => 'payment_reference',
            'show' => 1,
            'position' => 'right',
            'weight' => 4,
            'class' => '2-columns',
          ],
          'period' => [
            'title' => t("Periódo facturado"),
            'service_field' => 'period',
            'show' => 1,
            'position' => 'right',
            'weight' => 3,
            'class' => '2-columns',
          ],
          'status' => [
            'title' => t("Estado"),
            'service_field' => 'status',
            'show' => 1,
            'position' => 'left',
            'weight' => 5,
            'class' => '2-columns',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'url' => 'detalle-factura',
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'more_options' => [
            'details' => 1,
            'assign_contract_name' => 1,
            'approval' => 1,
            'complaint' => 1,
            'download_pdf' => 1,
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    // Vista de la factura.
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones tabla'),
      '#open' => TRUE,
    ];
    $form['table_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Field'), t('Show'), t('Weight'), t('Posición'), ''],
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
    uasort($table_fields, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);
    foreach ($table_fields as $id => $entity) {
      // TableDrag: Mark the invoice row as draggable.
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table_options']['table_fields']['#weight'] = $entity['weight'];
      // Some invoice columns containing raw markup.
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

    $others = $this->configuration['others']['config'];
    $form['others']['url'] = [
      '#type' => 'textfield',
      '#title' => t('Url para redirección de Detalles'),
      '#default_value' => $others['url'],
    ];
    $form['others']['more_options'] = [
      '#type' => 'details',
      '#title' => t('Configrar \'Más\' opciones'),
      '#description' => t('Ajuste de la visibilidad de los elementos'),
      '#open' => TRUE,
    ];
    $form['others']['more_options']['details'] = [
      '#type' => 'checkbox',
      '#title' => t('Detalles'),
      '#default_value' => $others['more_options']['details'],
    ];
    $form['others']['more_options']['assign_contract_name'] = [
      '#type' => 'checkbox',
      '#title' => t('Asignar nombre al contrato'),
      '#default_value' => $others['more_options']['assign_contract_name'],
    ];
    $form['others']['more_options']['approval'] = [
      '#type' => 'checkbox',
      '#title' => t('Enviar para aprobación'),
      '#default_value' => $others['more_options']['approval'],
    ];
    $form['others']['more_options']['complaint'] = [
      '#type' => 'checkbox',
      '#title' => t('Ingresar reclamo de factura'),
      '#default_value' => $others['more_options']['complaint'],
    ];
    $form['others']['more_options']['download_pdf'] = [
      '#type' => 'checkbox',
      '#title' => t('Descargar PDF'),
      '#default_value' => $others['more_options']['download_pdf'],
    ];
    $form = $this->instance->cardBlockForm($form['others'], $form['table_options']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(CurrentInvoiceBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'currentInvoiceBlock');
    $this->instance->setValue('directive', 'data-ng-current-invoice');
    $this->instance->setValue('class', 'block-currentinvoice');
    if (!isset($_SESSION['environment'])) {
      $_SESSION['environment'] = 'movil';
    }
    $invoices_filter_options = [
      'all' => t('Todas las facturas'),
      'slopes' => t('Facturas pendientes'),
      'overdue' => t('Facturas vencidas'),
      'paid' => t('Facturas pagadas'),
      'adjusted' => t('Facturas ajustadas'),
    ];
    if ($_SESSION['environment'] == 'movil') {
      unset($invoices_filter_options['adjusted']);
    }
    $order_filter_options = [
      'status' => t('Estado'),
      'date' => t('Fecha'),
      'min' => t('Menor valor'),
      'max' => t('Mayor valor'),
    ];
    $filters = [];
    $filter_mobile = [];
    $config_type = '';
    $config_type_send = isset($_SESSION['environment_' . $_SESSION['company']['nit']]) ? $_SESSION['environment_' . $_SESSION['company']['nit']] : 'movil';
    if (!isset($_SESSION['environment_' . $_SESSION['company']['nit']])) {
      $_SESSION['environment'] = 'movil';
    }
    if (isset($_SESSION['company']) && isset($_SESSION['environment'])) {
      if (($_SESSION['company']['environment'] != $_SESSION['environment']) && ($_SESSION['company']['environment'] != 'both')) {
        drupal_set_message('La empresa no posee facturas de tipo ' . $_SESSION['environment']);
      }
      else {
        $config_type = $_SESSION['environment'];
      }
    }
    elseif (isset($_SESSION['company'])) {
      $config_type = 'movil';
    }
    // Ordering filters_fields.
    $this->instance->ordering('filters_fields', 'filters_options');
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filters[$key_filter]['identifier'] = $key_filter;
        $filters[$key_filter]['label'] = $filter['title'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filters[$key_filter]['class'] = implode(" ", $classes);
        $filters[$key_filter]['service_field'] = $filter['service_field'];
        if ($key_filter == 'reference' || $key_filter == 'address' || $key_filter == 'contract') {
          $filters[$key_filter]['select_multiple'] = FALSE;
          $filters[$key_filter]['autocomplete_invoice'] = TRUE;
          if ($filter['max_length'] != 0 || $filter['max_length'] != '') {
            $filters[$key_filter]['validate_length'] = $filter['max_length'];
          }
        }
        else {
          $filters[$key_filter]['select_multiple'] = TRUE;
        }
        switch ($key_filter) {
          case 'invoices':
            $filters[$key_filter]['options'] = $invoices_filter_options;
            break;

          case 'order':
            $filters[$key_filter]['options'] = $order_filter_options;
            break;

          case 'address':
            if ($config_type == 'fijo') {
              $filters[$key_filter]['label'] = t('Dirección');
            }
            else {
              $filters[$key_filter]['label'] = t('Línea');
            }
            break;
        }
      }
    }
    $filter_mobile = [];
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) {
        $filter_mobile[$key_filter]['identifier'] = $key_filter;
        $filter_mobile[$key_filter]['label'] = $filter['title'];
        $classes = ["field-" . $filter['service_field'], $filter['class']];
        $filter_mobile[$key_filter]['class'] = implode(" ", $classes);
        $filter_mobile[$key_filter]['service_field'] = $filter['service_field'];
        if ($key_filter == 'reference') {
          $filter_mobile[$key_filter]['select_multiple'] = FALSE;
          $filter_mobile[$key_filter]['autocomplete_invoice'] = TRUE;
          if (isset($filter_mobile['max_length'])) {
            if ($filter_mobile['max_length'] != 0 || $filter['max_length'] != '') {
              $filter_mobile[$key_filter]['validate_length'] = $filter['max_length'];
            }
          }
        }
        else {
          $filter_mobile[$key_filter]['select_multiple'] = TRUE;
        }
        switch ($key_filter) {
          case 'invoices':
            $filter_mobile[$key_filter]['options'] = $invoices_filter_options;
            break;

          case 'order':
            $filter_mobile[$key_filter]['options'] = $order_filter_options;
            break;

          case 'address':
            if ($config_type == 'fijo') {
              $filters[$key_filter]['label'] = t('Dirección');
            }
            else {
              $filters[$key_filter]['label'] = t('Línea');
            }
            break;
        }
      }
    }
    $this->instance->setValue('filters', $filters);
    // Ordering table_fields.
    $this->instance->ordering('table_fields', 'table_options');
    $data = [];
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $data[$key_field]['key'] = $key_field;
        $data[$key_field]['label'] = $field['title'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $data[$key_field]['class'] = implode(" ", $classes);
        $data[$key_field]['service_field'] = $field['service_field'];
        $data[$key_field]['position'] = $field['position'];
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }
    $form = \Drupal::formBuilder()
      ->getForm('\Drupal\tbo_billing\Form\BillingTypeSelectButton');
    $config_form_pager = \Drupal::config("tbo_account.pagerformconfig");
    $config_pager['pages'] = $config_form_pager->get('pages');
    $config_pager['page_elements'] = $config_form_pager->get('page_elements');
    $title_invoice = 'FACTURA DE SERVICIOS FIJOS';
    $details_url = $this->configuration['others']['config']['url'];
    $type_display = $this->configuration['others']['config']['list'];
    $billing_url = '/tboapi/invoice/current?_format=json&' . 'billing_type=' . $config_type;
    $title_invoice = '';
    $type = $_SESSION['environment'];
    if ($config_type == 'fijo') {
      $title_invoice = t('FACTURA DE SERVICIOS FIJOS');
    }
    else {
      $title_invoice = t('FACTURA DE SERVICIOS MOVILES');
    }
    $contract = \Drupal::request()->query->get('contractId');
    if (isset($contract)) {
      $validate = FALSE;
      $billing_url .= '&billing_contract=' . $contract;
    }
    else {
      $validate = TRUE;
    }
    $build = [
      '#theme' => 'current_invoice',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#fields' => $data,
      '#class' => $this->instance->getValue('class'),
      '#id' => 'block-card',
      '#filters_mobile' => $filter_mobile,
      '#form' => $form,
      '#filters' => $filters,
      '#showoptions' => $validate,
      '#display' => $type_display,
      '#checked' => $config_type,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#more_options' => $this->configuration['others']['config']['more_options'],
      '#attached' => [
        'library' => [
          'tbo_billing/current-invoice',
        ],
      ],
      '#plugin_id' => $this->instance->getPluginId(),
    ];
    // Build.
    $this->instance->setValue('build', $build);
    $invoiceId = '';
    $detail = '';
    $isDetail = FALSE;
    if (\Drupal::service('path.current')->getPath() == '/detalle-factura') {
      $detail = $_SESSION['sendDetail']['paymentReference'];
      $invoiceId = $_SESSION['sendDetail']['invoiceId'];
      $isDetail = TRUE;
    }
    $other_config = [
      'details_url' => $details_url,
      'config_columns' => $this->instance->getValue('uuid'),
      'title_invoice' => $title_invoice,
      'contract' => $contract,
      'config_type' => $config_type,
      'config_type_send' => $config_type_send,
      'payment_reference' => $detail,
      'invoiceId' => $invoiceId,
      'isDetail' => $isDetail,
    ];
    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock($billing_url, $other_config);
    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'currentInvoiceBlock');
    // Return build.
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
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
