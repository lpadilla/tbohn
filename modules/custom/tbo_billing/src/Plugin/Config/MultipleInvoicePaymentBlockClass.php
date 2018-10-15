<?php

namespace Drupal\tbo_billing\Plugin\Config;

use Drupal\tbo_billing\Plugin\Block\MultipleInvoicePaymentBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'MultipleInvoicePaymentBlock' block.
 */
class MultipleInvoicePaymentBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * @param \Drupal\tbo_billing\Plugin\Block\MultipleInvoicePaymentBlock $instance
   * @param $config
   */
  public function setConfig(MultipleInvoicePaymentBlock &$instance, &$config) {
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
          'contract' => [
            'title' => t('Contrato'),
            'label' => 'Contrato',
            'service_field' => 'contract',
            'show' => 1,
            'show_mobile' => 0,
            'weight' => 1,
            'class' => '',
          ],
          'payment_reference' => [
            'title' => t('Referente de pago'),
            'label' => 'Referente de pago',
            'service_field' => 'payment_reference',
            'show' => 1,
            'show_mobile' => 1,
            'weight' => 2,
            'class' => '',
          ],
          'period' => [
            'title' => t('Período'),
            'label' => 'Período',
            'service_field' => 'period',
            'show' => 1,
            'show_mobile' => 0,
            'weight' => 3,
            'class' => '',
          ],
          'date_payment' => [
            'title' => t('Fecha límite de pago'),
            'label' => 'Fecha límite de pago',
            'service_field' => 'date_payment',
            'show' => 1,
            'show_mobile' => 1,
            'weight' => 4,
            'class' => '',
          ],
          'invoice_value2' => [
            'title' => t('Valor'),
            'label' => 'Valor',
            'service_field' => 'value',
            'show' => 1,
            'show_mobile' => 1,
            'weight' => 5,
            'class' => '',
          ],
          'close' => [
            'title' => t('Cerrar'),
            'label' => 'X',
            'service_field' => 'close',
            'show' => 1,
            'show_mobile' => 1,
            'weight' => 6,
            'class' => '',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'amount_invoices' => 1,
          'value_to_pay' => 1,
          'pay_button' => 1,
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'max_rows' => 1000,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones tabla'),
      '#description' => t('Para la versión mobile se tomaran los primeros tres campos que se configuren como visibles en la columna \'MOSTRAR MOBILE\''),
      '#open' => TRUE,
    ];
    $form['table_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Show'),
        t('Mostrar mobile'),
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
      $form['table_options']['table_fields'][$id]['show_mobile'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show_mobile'],
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

    $form['others'] = [
      '#type' => 'details',
      '#title' => t('Otras configuraciones'),
      '#open' => TRUE,
    ];
    $form['others']['amount_invoices'] = [
      '#type' => 'checkbox',
      '#title' => t('Cantidad de facturas seleccionadas/pendientes'),
      '#default_value' => $this->configuration['others']['config']['amount_invoices'],
    ];
    $form['others']['value_to_pay'] = [
      '#type' => 'checkbox',
      '#title' => t('Valor a pagar'),
      '#default_value' => $this->configuration['others']['config']['value_to_pay'],
    ];
    $form['others']['pay_button'] = [
      '#type' => 'checkbox',
      '#title' => t('Pagar/Pagar todas'),
      '#default_value' => $this->configuration['others']['config']['pay_button'],
    ];
    $form['others']['max_rows'] = [
      '#type' => 'number',
      '#title' => t('Máximo numero de registros en archivo excel'),
      '#default_value' => $this->configuration['others']['config']['max_rows'],
    ];

    $form = $this->instance->cardBlockForm($form['others'], $form['table_options']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(MultipleInvoicePaymentBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'MultipleInvoicePaymentBlock');
    $this->instance->setValue('directive', 'data-ng-multiple-invoice-payment');
    $this->instance->setValue('class', 'block-multiple-invoice-payment');
    $this->instance->ordering('table_options');
    $table = $this->configuration['table_options']['table_fields'];
    $this->configuration['others']['config']['texts'] = [
      'amount_invoices_1' => t('Facturas pendientes.'),
      'amount_invoices_2' => t('Facturas seleccionadas.'),
      'value' => t('Valor a pagar'),
      'button_1' => t('Pagar todas'),
      'button_2' => t('Pagar'),
    ];

    $table_mobile = [];

    foreach ($table as $key => $value) {
      if ($value['show_mobile'] == 1 && $key != 'close') {
        array_push($table_mobile, $key);
      }
    }

    $table_mobile = count($table_mobile) > 3 ? array_slice($table_mobile, 0, 3) : $table_mobile;

    array_push($table_mobile, 'close');

    $build = [
      '#theme' => 'multiple_invoice_payment',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#class' => $this->instance->getValue('class'),
      '#table' => $this->instance->getValue('table_fields'),
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#more' => $this->configuration['others']['config'],
      '#table_mobile' => $table_mobile,
      '#attached' => [
        'library' => [
          'tbo_billing/multiple-invoice-payment',
        ],
      ],
      '#plugin_id' => $this->instance->getPluginId(),
    ];
    $build['#cache']['max-age'] = 0;
    // Build.
    $this->instance->setValue('build', $build);

    // Max filas en excel.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_billing');
    $tempstore->set('max_rows', $this->configuration['others']['config']['max_rows']);

    $other_config = [
      'table' => $table,
      'table_mobile' => $table_mobile,
      'pay_from_summary' => isset($_GET['paymentM']) ? $_GET['paymentM'] : 0,
      'data_cache_payment' => isset($_SESSION['data_cache']) ? $_SESSION['data_cache'] : FALSE,
    ];
    $multiple_payment_url = '';
    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock($multiple_payment_url, $other_config);
    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'MultipleInvoicePaymentBlock');

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
