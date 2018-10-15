<?php

namespace Drupal\tbo_billing_hn\Plugin\Config;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_billing\Plugin\Config\CurrentInvoiceBlockClass;
use Drupal\tbo_billing\Plugin\Block\CurrentInvoiceBlock;
use Drupal\tbo_billing_hn\Plugin\Block\CurrentInvoiceHnBlock;
use Drupal\tbo_billing_hn\Services\CurrentInvoiceHnService;

/**
 * Manage config a 'CurrentInvoiceHnBlock' block.
 */
class CurrentInvoiceHnBlockClass extends CurrentInvoiceBlockClass {
  protected $instance;
  protected $configuration;
  /**
   * @param CurrentInvoiceBlock $instance
   * @param $config
   */
  public function setConfig(CurrentInvoiceBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }
  
  public function defaultConfiguration() {
    return array(
      'filters_options' => [
        'filters_fields' => [
          'billingAccount' => ['title' => t("Grupo"),'label' => t("Grupo"),'service_field' => 'billingAccount', 
            'show' => 1, 
            'weight' => 3, 
            'class' => '2-columns'],
          'plan' => [
            'title' => t("Plan"),
            'label' => t("Plan"),  
            'service_field' => 'plan', 
            'show' => 1, 
            'weight' => 3, 
            'class' => '2-columns'],
          'address' => [          
            'title' => t("Dirección"), 
            'label' => t(""), 
            'service_field' => 'address', 
            'show' => 1, 
            'weight' => 1, 
            'class' => '2-columns', 
            'max_length' => 200],
          'linea' => [
            'title' => t("Línea"), 
            'label' => t("Numero"), 
            'service_field' => 'linea', 
            'show' => 1, 
            'weight' => 2, 
            'class' => '2-columns'],
          'reference' => [
            'title' => t(" "), 
            'label' => t("Búsqueda"), 
            '#placeholder' => t('Búsqueda'),
            'service_field' => 'reference', 
            'show' => 1, 
            'weight' => 2, 
            'class' => '2-columns icon-search', 
            'max_length' => 200],
          'contract' => [
            'title' => t("Contrato"), 
            'label' => t("Contrato"), 
            'service_field' => 'contract', 
            'show' => 1, 
            'weight' => 3, 
            'class' => '2-columns', 
            'max_length' => 200],
          'invoices' => [
            'title' => t("Estado"), 
            'label' => t("Estado"), 
            'service_field' => 'invoice', 
            'show' => 1, 
            'weight' => 3, 
            'class' => '2-columns'],
          'order' => [
            'title' => t("Ordenar por estado"), 
            'label' => t("Ordenar"), 
            'service_field' => 'order', 
            'show' => 1, 
            'weight' => 3, 
            'class' => '2-columns'],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'invoice_value' => ['title' => t("Valor de la factura"), 'service_field' => 'invoice_value', 'show' => 1, 'position' => 'left', 'weight' => 2, 'class' => '2-columns'],
          'date_payment' => ['title' => t("Fecha de pago"), 'service_field' => 'date_payment', 'show' => 1,  'position' => 'left', 'weight' => 3, 'class' => '2-columns'],
          'contract' => ['title' => t("Num de Línea"), 'service_field' => 'contract', 'show' => 1,'position' => 'right', 'weight' => 3, 'class' => '2-columns'],
          'payment_reference' => ['title' => t("Referencia de pago"), 'service_field' => 'payment_reference', 'show' => 1,  'position' => 'right', 'weight' => 4, 'class' => '2-columns'],
          'invoiceId' => ['title' => t("Número de factura"), 'service_field' => 'invoiceId', 'show' => 1, 'position' => 'left', 'weight' => 2, 'class' => '2-columns'],
          'period' => ['title' => t("Período facturado"), 'service_field' => 'period', 'show' => 1,  'position' => 'right', 'weight' => 3, 'class' => '2-columns'],
          'cai' => ['title' => t("CAI"), 'service_field' => 'cai', 'show' => 1, 'position' => 'right', 'weight' => 1, 'class' => '2-columns'],
          'address' => ['title' => t("Dirección"), 'service_field' => 'address', 'show' => 1, 'position' => 'right', 'weight' => 1, 'class' => '2-columns'],
          'status' => ['title' => t("Estado"), 'service_field' => 'status', 'show' => 1,  'position' => 'left', 'weight' => 5, 'class' => '2-columns'],        
          
        ],
      ],
      'others' => [
        'config' => [
          'environment' => 'movil',
          'url' => 'detalle-factura',
          'money' => '$',
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],

          'more_options' => [
            'simCard_change' => 1,
            'details' => 1,
            'assign_contract_name' => 1,
            'approval' => 1,
            'complaint' => 1,
            'download_pdf' => 1,
          ],
        ],
      ],
    );
  }


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
    uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
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
    $form['others']['more_options']['simCard_change'] = [
      '#type' => 'checkbox',
      '#title' => t('Cambio de SimCard'),
      '#default_value' => $others['more_options']['simCard_change'],
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
    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;
   
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    
    $this->instance->setValue('config_name', 'currentInvoiceBlock');
    $this->instance->setValue('directive', 'data-ng-current-invoice');
    $this->instance->setValue('class', 'block-currentinvoice');
    if (!isset($_SESSION['environment'])) {
      $_SESSION['environment'] = 'movil';
    }
    $invoices_filter_options = [      
      'paid' => t('Facturas Pagadas'),
      'slopes' => t('Facturas Pendientes'),
      'overdue' => t('Facturas Vencidas'),      
      'adjusted' => t('Facturas Ajustadas'),
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
       
        $config_type = 'movil'; 
      }
      else {
        $config_type = $_SESSION['environment'];
      }
    }
    elseif (isset($_SESSION['company'])) {
      $config_type = 'movil';
    }
    
    $this->instance->ordering('filters_fields', 'filters_options');
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) 
    {
      if ($filter['show'] == 1) 
      {
        if ( $config_type == 'fijo' || $config_type == 'movil')
        {
          $filters[$key_filter]['identifier'] = $key_filter;
          $filters[$key_filter]['label'] = $filter['title'];
          $filters[$key_filter]['placeholder'] = isset($filter['label']) ? $filter['label']:'';          
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
            case 'linea':
              $filters[$key_filter]['options'] = $data_filter_line;
              break;   
            case 'address':
              if ($config_type == 'fijo') {
                $filters[$key_filter]['label'] = t('Dirección');
              }
              else {
                $filters[$key_filter]['label'] = t('');
              }
              break;      
          }
        }
      }
    }
    $filter_mobile = [];
    foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
      if ($filter['show'] == 1) 
      {
        if ($config_type == 'fijo' ||  $config_type == 'movil')
        {
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
            case 'linea':
              $filter_mobile[$key_filter]['options'] = $order_filter_options;
              break;  
             
          }
        }
      }
    }
    $this->instance->setValue('filters', $filters);
    //Ordering table_fields
    $this->instance->ordering('table_fields', 'table_options');
    $data = array();
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
    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_billing\Form\BillingTypeSelectButton');
    $config_form_pager = \Drupal::config("tbo_account.pagerformconfig");
    $config_pager['pages'] = $config_form_pager->get('pages');
    $config_pager['page_elements'] = $config_form_pager->get('page_elements');
    $title_invoice = 'FACTURA DE SERVICIOS FIJOS';
    $details_url = $this->configuration['others']['config']['url'];
    
    $billing_url = '/tboapi/invoice/hn/current?_format=json&' . 'billing_type=' . $config_type;

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
      $validate = false;
      $billing_url .= '&billing_contract=' . $contract;
    }
    else {
      $validate = true;
    }
    $build = array(
      '#theme' => 'current_invoice_hn',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#fields' => $data,
      '#class' => $this->instance->getValue('class'),
      '#id' => 'block-card',
      '#filters_mobile' => $filter_mobile,
      '#form' => $form,
      '#filters' => $filters,
      '#showoptions' => $validate,// '#display' => $type_display,
      '#checked' => $config_type,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#more_options' => $this->configuration['others']['config']['more_options'],
      '#attached' => array(
        'library' => array(
          'tbo_billing_hn/current-invoice-hn'          
        ),
      ),
    );
    
    //build
    $this->instance->setValue('build', $build);
    $money = $this->configuration['others']['config']['money'];
    $invoiceId = '';
    $detail = '';
    if (\Drupal::service('path.current')->getPath() == '/detalle-factura') {
      $detail = $_SESSION['sendDetail']['paymentReference'];
      $invoiceId = $_SESSION['sendDetail']['invoiceId'];
    }
    $other_config = [
      'details_url' => $details_url,
      'config_columns' => $this->instance->getValue('uuid'),
      'title_invoice' => $title_invoice,
      'contract' => $contract,
      'config_type' => $config_type,
      'config_type_send' => $config_type_send,
      'money' => $money,
      'payment_reference' => $detail,
      'invoiceId' => $invoiceId,
    ];
   
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($billing_url, $other_config);    
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'currentInvoiceBlock');
    //return build
    return $this->instance->getValue('build');
  }
  
}