<?php

namespace Drupal\tbo_billing_hn\Plugin\Config\Block;


use Drupal\tbo_billing\Plugin\Config\Block\InvoiceHistoryBlockClass;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_billing\Plugin\Block\InvoiceHistoryBlock;

/**
 * Manage config a 'InvoiceHistoryBlockClass' block.
 */
class InvoiceHistoryHnBlockClass extends InvoiceHistoryBlockClass {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'table_options' => [
        'table_fields' => [
          'period' => ['title' => t('Período'), 'label' => t('Período'), 'service_field' => 'period', 'show' => 1, 'weight' => 1,],
          'invoiceId' => ['title' => t('Número de factura'), 'label' => t('Número de factura'), 'service_field' => 'invoiceId', 'show' => 1, 'weight' => 1,],
          'contractId' => ['title' => t('Contrato'), 'label' => t('Contrato'), 'service_field' => 'contractId', 'show' => 1, 'weight' => 1,],
          'dueDate' => ['title' => t('Fecha de vencimiento'), 'label' => t('Fecha de vencimiento'), 'service_field' => 'dueDate', 'show' => 1, 'weight' => 2,],
          'hasPayment' => ['title' => t('Estado'), 'label' => t('Estado'), 'service_field' => 'status', 'show' => 1, 'weight' => 3,],
          'dueAmount' => ['title' => t('Valor'), 'label' => t('Valor'), 'service_field' => 'invoiceAmount', 'show' => 1, 'weight' => 4,],
          'options' => ['title' => t('Descargar'), 'label' => t('Descargar'), 'service_field' => 'options', 'show' => 1, 'weight' => 5,],
        ],
      ],
      'others' => [
        'config' => [
          'details' => 1,
          'title_fixed' => 'Contrato',
          'title_movil' => 'Linea',
          'paginate' => [
            'number_rows_pages' => 10,
          ],
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
        ],
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(InvoiceHistoryBlock &$instance, &$config) {
    //Set values for duplicates cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'invoicesHistoryBlock');
    $this->instance->setValue('directive', 'data-ng-invoices-history');
    $this->instance->setValue('class', 'historical-invoice historical_invoice');

    $columns = [];
    //Ordering table_fields
    $this->instance->ordering('table_fields', 'table_options');

    $headers_table = $columns_table = [];
    $opions_enable = FALSE;
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $headers_table[$key_field]['identifier'] = $key_field;
        $headers_table[$key_field]['label'] = $field['title'];

        if ($key_field == 'contractId') {
          if ($_SESSION['environment'] != 'fijo') {
            $headers_table[$key_field]['label'] = t('Línea');
          }
        }

        $classes = ["field-" . $field['service_field'], $field['class']];
        $headers_table[$key_field]['class'] = implode(" ", $classes);
        $headers_table[$key_field]['service_field'] = $field['service_field'];
        unset($classes);

        if ($field['service_field'] != 'options') {
          $columns_table[$key_field]['service_field'] = $field['service_field'];
        }
        else {
          $opions_enable = TRUE;
        }
      }
      else {
        unset($field[$key_field]);
      }
    }

    $title_colum = 'Linea/Contrato';
    if ($_SESSION['environment'] == 'fijo') {
      $title_colum = $this->configuration['others']['config']['title_fixed'];
    }
    elseif ($_SESSION['environment'] == 'movil') {
      $title_colum = $this->configuration['others']['config']['title_movil'];
    }

    //Get show detalle
    $detail = null;
    if ($this->configuration['others']['config']['details'] == 1) {
      $detail = 1;
    }

    //Load title view
    $title = null;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    $paramethers = [
      'theme' => 'invoice_history_hn',
      'columns' => $columns_table,
      'library' => 'tbo_billing/invoices-history',
    ];

    $others = [
      '#title_view' => $title,
      '#headers_table' => $headers_table,
      '#opions_enable' => $opions_enable,
      '#show_detail' => $detail,
      '#title_colum' => $title_colum,
      '#environment' => $_SESSION['environment'],
    ];

    $this->instance->cardBuildVarBuild($paramethers, $others);

    //Generate config por angular
    $others = [
      'environment' => $_SESSION['environment'],
    ];
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock('tboapi/billing/history/hn?_format=json', $others);

    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'invoicesHistoryBlock');

    return $this->instance->getValue('build');
  }

}