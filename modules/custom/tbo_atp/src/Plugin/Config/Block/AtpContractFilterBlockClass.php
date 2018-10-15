<?php

namespace Drupal\tbo_atp\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_atp\Plugin\Block\AtpContractFilterBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Manage config a 'AtpContractFilterBlockClass' block.
 */
class AtpContractFilterBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param AtpContractFilterBlock $instance
   * @param $config
   */
  public function setConfig(AtpContractFilterBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'download' => 'csv',
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_card' => 1,
          ],
        ],
      ],
      'show_btn_invoice_detail' => TRUE,
      'show_btn_account_detail' => TRUE,
      'label_btn_invoice_detail' => 'DETALLE FACTURA',
      'label_btn_account_detail' => 'DETALLE CUENTA',
      'error_message' => 'Ocurrio un error al generar el archivo, por favor intente más tarde.',
    ];
  }

  /*
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form = $this->instance->cardBlockForm();

    $form['download'] = [
      '#type' => 'select',
      '#title' => t('Formato de archivo para detalle de factura'),
      '#options' => [
        'csv' => t('CSV'),
        'txt' => t('TXT'),
        'xlsx' => t('XLS'),
      ],
      '#default_value' => $this->configuration['download'],
    ];

    $form['error_message'] = [
      '#type' => 'textfield',
      '#title' => t('Mensaje de error'),
      '#default_value' => $this->configuration['error_message'],
    ];
    // (Configuraciones para boton detalle cuenta)
    $form['details_opt_account_detail'] = array(
      '#type' => 'details',
      '#title' => t('Configuraciones para botón DETALLE CUENTA'),
      '#open' => TRUE,
    );
    $form['details_opt_account_detail']['show_btn_account_detail'] = [
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['show_btn_account_detail'],
      '#title' => 'Mostrar botón DETALLE CUENTA',
    ];
    $form['details_opt_account_detail']['label_btn_account_detail'] = [
      '#type' => 'textfield',
      '#default_value' => $this->configuration['label_btn_account_detail'],
      '#title' => 'Label del botón',
    ];
    // (Configuraciones para boton detalle factura)
    $form['details_opt_invoice_detail'] = array(
      '#type' => 'details',
      '#title' => t('Configuraciones para botón DETALLE FACTURA'),
      '#open' => TRUE,
    );
    $form['details_opt_invoice_detail']['show_btn_invoice_detail'] = [
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['show_btn_invoice_detail'],
      '#title' => 'Mostrar botón DETALLE FACTURA',
    ];
    $form['details_opt_invoice_detail']['label_btn_invoice_detail'] = [
      '#type' => 'textfield',
      '#default_value' => $this->configuration['label_btn_invoice_detail'],
      '#title' => 'Label del botón',
    ];
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['download'] = $form_state->getValue('download');
    $this->configuration['error_message'] = $form_state->getValue('error_message');
    $this->configuration['label_btn_account_detail'] = $form_state->getValue(["details_opt_account_detail", "label_btn_account_detail"]);
    $this->configuration['label_btn_invoice_detail'] = $form_state->getValue(['details_opt_invoice_detail', 'label_btn_invoice_detail']);
    $this->configuration['show_btn_account_detail'] = $form_state->getValue(['details_opt_account_detail', 'show_btn_account_detail']);
    $this->configuration['show_btn_invoice_detail'] = $form_state->getValue(['details_opt_invoice_detail', 'show_btn_invoice_detail']);

  }

  /**
   * {@inheritdoc}
   */
  public function build(AtpContractFilterBlock &$instance, $configuration) {
    //Set data uuid, generate filters_fields, generate table_fields
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'atpContractFilterBlock');
    $instance->setValue('directive', 'data-ng-atp-contract-filter');
    $instance->setValue('class', 'block-atp-contract-filter');
    $instance->setValue('label_btn_account_detail', 'label_btn_account_detail');
    $instance->setValue('label_btn_invoice_detail', 'label_btn_invoice_detail');
    $instance->setValue('show_btn_account_detail', 'show_btn_account_detail');
    $instance->setValue('show_btn_invoice_detail', 'show_btn_invoice_detail');

    $parameters = [
      'library' => 'tbo_atp/contract_filter',
      'theme' => 'atp_contract_filter',
    ];

    $val_atp = \Drupal::service('tbo_atp.general_service')->validateAtpServices();

    $other_config = [
      '#download' => $configuration['download'],
      '#card_margin' => $configuration['others']['config']['show_margin']['show_margin_card'],
      '#val_atp' => $val_atp,
      '#label_btn_account_detail' => $configuration['label_btn_account_detail'],
      '#label_btn_invoice_detail' => $configuration['label_btn_invoice_detail'],
      '#show_btn_account_detail' => $configuration['show_btn_account_detail'],
      '#show_btn_invoice_detail' => $configuration['show_btn_invoice_detail'],
    ];

    $instance->cardBuildVarBuild($parameters, $other_config);

    $other = [
      'error_message' => $this->configuration['error_message'],
    ];

    $config = $instance->cardBuildConfigBlock('/tbo-atp/contract-filter?_format=json', $other);

    $instance->cardBuildAddConfigDirective($config, 'atpContractFilterBlock');

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

    if ((in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles))/* && ($_SESSION['company']['environment'] == 'movil' || $_SESSION['company']['environment'] == 'both')*/) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }
}
