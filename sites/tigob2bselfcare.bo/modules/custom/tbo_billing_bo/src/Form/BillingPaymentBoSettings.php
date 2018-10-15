<?php

namespace Drupal\tbo_billing_bo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class BillingPaymentBoSettings.
 *
 * @package Drupal\tbo_billing_bo\Form
 */
class BillingPaymentBoSettings extends ConfigFormBase
{
  
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'tbo_billing_payment_bo_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_billing_bo.bill_payment_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('tbo_billing_bo.bill_payment_settings');

    $form["#tree"] = true;
    $form['bootstrap'] = [
      '#type' => 'vertical_tabs',
      '#prefix' => '<h2><small>' . t('TBO Configuración de Facturación BO') . '</small></h2>',
      '#weight' => -10,
      '#default_tab' => $config->get('active_tab'),
    ];


    $group = "visualizacion";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Parametros'),
      '#open' => TRUE,
      '#group' => 'bootstrap'
    ];

    $form[$group]['serviceparam'] = array(
      '#type' => 'details',
      '#title' => t('Service Param settings'),
      '#description' => t('Parametros para consumir servicios web para factura pagina principal Bolivia'),
      '#open' => TRUE,
    );

    /*$form[$group]['serviceparam']['billingquantity'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('quantity'),
      '#default_value' => $config->get($group)['serviceparam']['billingquantity'],
    );*/
  
  	$form[$group]['serviceparam']['billingoffset'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Offset'),
      '#default_value' => $config->get($group)['serviceparam']['billingoffset'],
    );
  	
    $form[$group]['serviceparam']['billinglimit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $config->get($group)['serviceparam']['billinglimit'],
    );
    $form[$group]['serviceparam']['billingpc'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Quantity PC(Pendientes por cobrar): valor que limita la cantidad de meses a considerar para buscar contratos con Facturas que tienen estatus Pendientes por cobrar. Maximo valor permitido es 12'),
      '#default_value' => $config->get($group)['serviceparam']['billingpc'],
    );
    $form[$group]['serviceparam']['billingpc_cut'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Cantidad Facturas PC (Pendientes por Cobrar) que se mostrarán en la página por cada contrato asociado a la empresa'),
      '#default_value' => $config->get($group)['serviceparam']['billingpc_cut'],
    );
    $form[$group]['serviceparam']['billingca'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Quantity CA(Canceladas o pagadas): valor que limita la cantidad de meses a considerar para buscar contratos con Facturas que tienen estatus Canceladas o Pagadas. Minimo valor permitido es 6, maximo valor es 12'),
      '#default_value' => $config->get($group)['serviceparam']['billingca'],
    );
    $form[$group]['serviceparam']['billingca_cut'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Cantidad de Facturas CA (Canceladas o Pagadas) que se mostraran en la página por cada contrato asociado a la empresa'),
      '#default_value' => $config->get($group)['serviceparam']['billingca_cut'],
    );
    
    
    
    $form[$group]['servicehistoryparam'] = array(
      '#type' => 'details',
      '#title' => t('Service for History Param settings'),
      '#description' => t('Parametros para consumir servicios web para factura detalle Bolivia'),
      '#open' => TRUE,
    );

    $form[$group]['servicehistoryparam']['billingquantityhistory'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('quantity'),
      '#default_value' => $config->get($group)['servicehistoryparam']['billingquantityhistory'],
    );
 
    $form[$group]['servicehistoryparam']['billingoffsethistory'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Offset'),
      '#default_value' => $config->get($group)['servicehistoryparam']['billingoffsethistory'],
    );
    
    $form[$group]['servicehistoryparam']['billinglimithistory'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
    );
    
    
    
    $form[$group]['servicedashboardparam'] = array(
      '#type' => 'details',
      '#title' => t('Service for Dashboard Param settings'),
      '#description' => t('Parametros para consumir servicios web para factura en Dashboard Bolivia'),
      '#open' => TRUE,
    );

    $form[$group]['servicedashboardparam']['billingquantitydashboard'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('quantity'),
      '#default_value' => $config->get($group)['servicedashboardparam']['billingquantitydashboard'],
    );
 
    $form[$group]['servicedashboardparam']['billingoffsetdashboard'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Offset'),
      '#default_value' => $config->get($group)['servicedashboardparam']['billingoffsetdashboard'],
    );
    
    $form[$group]['servicedashboardparam']['billinglimitdashboard'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $config->get($group)['servicedashboardparam']['billinglimitdashboard'],
    );
    
    
    $form[$group]['servicedashboardparam']['contractquantitydashboardcutoff'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Contract Quantity Dashboard Cutoff'),
      '#default_value' => $config->get($group)['servicedashboardparam']['contractquantitydashboardcutoff'],
    );
    
    /*
    $form[$group]['servicestokens'] = array(
      '#type' => 'details',
      '#title' => t('Token for all Services'),
      '#description' => t('Tokens para cada uno de los servicios a consumir, que se actualiazará cada 2 o 3 dias'),
      '#open' => TRUE,
    );

    $form[$group]['servicestokens']['getBalanceInquiry'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('getBalanceInquiry [environment: PROD]'),
      '#default_value' => $config->get($group)['servicestokens']['getBalanceInquiry'],
    );

    $form[$group]['servicestokens']['PostTransferBalance'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('PostTransferBalance [environment: PROD]'),
      '#default_value' => $config->get($group)['servicestokens']['PostTransferBalance'],
    );
 
    */
 

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);
    // Retrieve the configuration
    $this->config('tbo_billing_bo.bill_payment_settings')
      ->set('visualizacion', $form_state->getValue('visualizacion'))
      ->save();

    drupal_set_message($this->t('The configuration options have been saved!'));
  }
}