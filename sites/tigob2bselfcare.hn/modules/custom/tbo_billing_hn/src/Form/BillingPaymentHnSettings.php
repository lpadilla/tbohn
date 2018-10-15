<?php

namespace Drupal\tbo_billing_hn\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class BillingPaymentHnSettings.
 *
 * @package Drupal\tbo_billing_hn\Form
 */
class BillingPaymentHnSettings extends ConfigFormBase
{
  
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'tbo_billing_payment_hn_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_billing_hn.bill_payment_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('tbo_billing_hn.bill_payment_settings');

    $form["#tree"] = true;
    $form['bootstrap'] = [
      '#type' => 'vertical_tabs',
      '#prefix' => '<h2><small>' . t('TBO Configuración de Facturación HN') . '</small></h2>',
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
      '#description' => t('Parametros para consumir servicios web para factura pagina principal Honduras'),
      '#open' => TRUE,
    );

    $form[$group]['serviceparam']['billingquantity'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('quantity'),
      '#default_value' => $config->get($group)['serviceparam']['billingquantity'],
    );
 
    $form[$group]['serviceparam']['billinglimit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $config->get($group)['serviceparam']['billinglimit'],
    );
    
    
    $form[$group]['servicehistoryparam'] = array(
      '#type' => 'details',
      '#title' => t('Service for History Param settings'),
      '#description' => t('Parametros para consumir servicios web para factura detalle Honduras'),
      '#open' => TRUE,
    );

    $form[$group]['servicehistoryparam']['billingquantityhistory'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('quantity'),
      '#default_value' => $config->get($group)['servicehistoryparam']['billingquantityhistory'],
    );
 
    $form[$group]['servicehistoryparam']['billinglimithistory'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $config->get($group)['servicehistoryparam']['billinglimithistory'],
    );

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
    $this->config('tbo_billing_hn.bill_payment_settings')
      ->set('visualizacion', $form_state->getValue('visualizacion'))
      ->save();

    drupal_set_message($this->t('The configuration options have been saved!'));
  }
}