<?php

namespace Drupal\tbo_billing\Plugin\Config\Block;

use Drupal\tbo_billing\Plugin\Block\BillingSummaryBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'BillingSummaryBlock' block.
 */
class BillingSummaryBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_billing\Plugin\Block\BillingSummaryBlock $instance
   * @param $config
   */
  public function setConfig(BillingSummaryBlock &$instance, &$config) {
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
          'total' => ['title' => t("Total a pagar"), 'label' => 'Total a pagar', 'service_field' => 'total', 'show' => 1, 'weight' => 1, 'class' => 'double-top-and-bottom-padding'],
          'invoices' => ['title' => t("Facturas pendientes"), 'label' => 'Facturas pendientes', 'service_field' => 'invoices', 'show' => 1, 'weight' => 2, 'class' => 'double-top-and-bottom-padding'],
          'icon' => ['title' => t("Icono"), 'label' => 'Icono', 'service_field' => 'icon', 'show' => 1, 'weight' => 3, 'class' => 'double-top-and-bottom-padding'],
          'details' => ['title' => t("Detalles"), 'label' => 'Detalles', 'service_field' => 'details', 'show' => 1, 'weight' => 4, 'class' => 'double-top-and-bottom-padding'],
          'pay' => ['title' => t("Pagar"), 'label' => 'Pagar', 'service_field' => 'pay', 'show' => 1, 'weight' => 5, 'class' => 'double-top-and-bottom-padding'],
        ],
      ],
      'others' => [
        'config' => [
          'url_details' => '',
          'url_payment' => '',
          'type_wrapper' => [
            'type' => 'mobile',
          ],
          'noServices_wrapper' => [
            'description' => t('Descripción del card'),
            'label_button' => t('Texto del botón'),
            'url_button' => '',
          ],
          'segment' => 0,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm(&$form, &$form_state, $configuration) {
    $this->configuration = $configuration;
    $others = $this->configuration['others']['config'];
    $field['others']['config']['url_details'] = [
      '#type' => 'url',
      '#title' => t('Url detalles'),
      '#default_value' => $others['url_details'],
      '#required' => TRUE,
    ];

    $field['others']['config']['url_payment'] = [
      '#type' => 'url',
      '#title' => t('Url pago'),
      '#default_value' => $others['url_payment'],
      '#required' => TRUE,
    ];

    $field['others']['config']['type_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => t('Tipo de servicio'),
    ];

    $field['others']['config']['type_wrapper']['type'] = [
      '#type' => 'radios',
      '#title' => t('Tipo de servicio'),
      '#title_display' => 'invisible',
      '#options' => [
        'movil' => t('Móvil'),
        'fijo' => t('Fijo'),
      ],
      '#default_value' => $others['type_wrapper']['type'],
      '#required' => TRUE,
    ];

    $field['others']['config']['noServices_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => t('Card sin servicios contratados'),
    ];

    $field['others']['config']['noServices_wrapper']['description'] = [
      '#type' => 'textfield',
      '#title' => t('Descripción'),
      '#maxlength' => 130,
      '#default_value' => $others['noServices_wrapper']['description'],
      '#required' => TRUE,
    ];

    $field['others']['config']['noServices_wrapper']['label_button'] = [
      '#type' => 'textfield',
      '#title' => t('Texto para la redirección'),
      '#maxlength' => 30,
      '#default_value' => $others['noServices_wrapper']['label_button'],
      '#required' => TRUE,
    ];

    $field['others']['config']['noServices_wrapper']['url_button'] = [
      '#type' => 'url',
      '#title' => t('Url de la redirección'),
      '#default_value' => $others['noServices_wrapper']['url_button'],
      '#required' => TRUE,
    ];

    $field['others']['config']['segment'] = [
      '#type' => 'checkbox',
      '#title' => t('Activar envio de segment'),
      '#default_value' => $this->configuration['segment'],
    ];

    $form = $this->instance->cardBlockForm($field['others']['config']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(BillingSummaryBlock &$instance, $configuration) {
    // Set data uuid, generate filters_fields, generate table_fields.
    $typeService = $configuration['others']['config']['type_wrapper']['type'];
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'billingSummaryBlock');
    $instance->setValue('class', 'block-billing-summary-message');
    $library = $title_no_services = '';

    if ($typeService == 'movil') {
      $library = 'tbo_billing/billing-summary';
      $instance->setValue('directive', 'data-ng-billing-summary');
      $title_no_services = t('Servicios móviles');
    }
    else {
      $library = 'tbo_billing/billing-summary-fixed';
      $instance->setValue('directive', 'data-ng-billing-summary-fixed');
      $title_no_services = t('Servicios fijos');
    }

    // Set session var.
    $instance->cardBuildSession();

    $parameters = [
      'theme' => 'billing_summary',
      'library' => $library,
    ];

    usort($configuration['table_options']['table_fields'], function ($a1, $a2) {
      $v1 = $a1['weight'];
      $v2 = $a2['weight'];
      // $v2 - $v1 to reverse direction.
      return $v1 - $v2;
    });
    $display = '';

    if ((isset($_SESSION['company'])) && ($_SESSION['company']['environment'] == $typeService || $_SESSION['company']['environment'] == 'both')) {
      $display = 'services';
    }
    else {
      $display = 'no-services';
    }

    $others = [
      '#display' => $display,
      '#fields' => $configuration['table_options']['table_fields'],
      '#type' => $typeService,
      '#url' => $configuration['others']['config']['url_details'],
      '#url_payment' => $configuration['others']['config']['url_payment'],
      '#description' => $configuration['others']['config']['noServices_wrapper']['description'],
      '#button_text' => $configuration['others']['config']['noServices_wrapper']['label_button'],
      '#button_url' => $configuration['others']['config']['noServices_wrapper']['url_button'],
      '#titleNoServices' => $title_no_services,
      '#segment' => $configuration['segment'],
    ];

    $other_config = [
      'type' => $typeService,
    ];

    $config_block = $instance->cardBuildConfigBlock('/tboapi/billing/summary?_format=json', $other_config);
    $instance->cardBuildVarBuild($parameters, $others);
    $instance->cardBuildAddConfigDirective($config_block);

    // Guardando log auditoria
    // Load fields account.
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }

    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data_log[].
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturación',
      'description' => t('Usuario consulta resumen de cuenta'),
      'details' => 'Usuario ' . $name . ' ' . 'consultó el resumen de cuenta',
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);

    return $instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit(&$form, &$form_state, &$configuration) {
    $configuration['table_options'] = $form_state->getValue(['table_options']);
    $configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $configuration['others_display'] = $form_state->getValue(['others_display']);
    $configuration['buttons'] = $form_state->getValue(['buttons']);
    $configuration['others'] = $form_state->getValue(['others']);
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
