<?php

namespace Drupal\tbo_billing_bo\Plugin\Config\Block;

use Drupal\tbo_billing\Plugin\Config\Block\BillingSummaryBlockClass;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_billing\Plugin\Block\BillingSummaryBlock;

class FacturacionSummaryBlockClass extends BillingSummaryBlockClass {

	public function defaultConfiguration() {
	    return [
	      'filters_options' => [
	        'filters_fields' => [],
	      ],
	      'table_options' => [
        	'table_fields' => [
	          'total' => ['title' => t("Total a pagar"), 'label' => t('Total a pagar'), 'service_field' => 'total', 'show' => 1, 'weight' => 1, 'class' => 'double-top-and-bottom-padding'],
	          'invoices' => ['title' => t("Facturas pendientes"),'label' => t('Facturas pendientes'), 'service_field' => 'invoices', 'show' => 1, 'weight' => 2, 'class' => 'double-top-and-bottom-padding'],
	          'icon' => ['title' => t("Icono"), 'service_field' => 'icon', 'show' => 1, 'weight' => 3, 'class' => 'double-top-and-bottom-padding'],
	          'details' => ['title' => t("Detalles"), 'label' => t('Detalles'), 'service_field' => 'details', 'show' => 1, 'weight' => 4, 'class' => 'double-top-and-bottom-padding'],
	          
	        ],
	      ],
	      'others' => [
	        'config' => [
	          'url_details' => '',
	          'type_wrapper' => [
	            'type' => 'movil',
	          ],
	          
	          'segment' => 0,
            'titulo' => [
              'title_factura' => t('Facturación'),
            ]
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

    $field['others']['config']['titulo'] = [
      '#type' => 'fieldset',
      '#title' => t('Titulo bloque Facturación'),
    ];

    $field['others']['config']['titulo']['title_factura'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre título'),
      '#default_value' => $others['titulo']['title_factura'],
      '#description' => t('Ingrese el texto para el título'),
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
    $instance->setValue('config_name', 'facturcionSummaryBlock');
    $instance->setValue('class', 'block-facturacion-summary-message');
    $library = $title_no_services = '';
    

    if ($typeService == 'movil') {
      $library = 'tbo_billing_bo/facturacion-summary';
      $instance->setValue('directive', 'data-ng-facturacion-summary');
      $title_no_services = t('Servicios móviles');
    }
    else {
      $library = 'tbo_billing_bo/facturacion-summary-fixed';
      $instance->setValue('directive', 'data-ng-facturacion-summary-fixed');
      $title_no_services = t('Servicios fijos');
    }

    // Set session var.
    $instance->cardBuildSession();

    $parameters = [
      'theme' => 'facturacion_summary',
      'library' => $library,
    ];

    usort($configuration['table_options']['table_fields'], function ($a1, $a2) {
      $v1 = $a1['weight'];
      $v2 = $a2['weight'];
      
      return $v1 - $v2;
    });
    $display = '';

    if ((isset($_SESSION['company']['client_code'])) ) {
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
      '#titleNoServices' => $title_no_services,
      '#segment' => $configuration['others']['config']['segment'],
      '#title' => $configuration['others']['config']['titulo']['title_factura'],
    ];

    
    
    if($_SESSION['company']['client_code']){
      $clients =$_SESSION['company']['client_code'];
    }

    $other_config = [
      'type' => $typeService,
      'clients' => $clients,
    ];

    $instance->cardBuildVarBuild($parameters, $others);
    $config_block = $instance->cardBuildConfigBlock('/tboapi/billing/facturacion_summary?_format=json', $other_config);
    
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
      'event_type' => 'Facturación y Contratos',
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