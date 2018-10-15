<?php

namespace Drupal\tbo_billing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BillingPaymentSettings.
 *
 * @package Drupal\tbo_billing\Form
 */
class BillingPaymentSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_billing_payment_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_billing.bill_payment_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_billing.bill_payment_settings');

    $form["#tree"] = TRUE;
    $form['bootstrap'] = [
      '#type' => 'vertical_tabs',
      '#prefix' => '<h2><small>' . t('TBO Configuración de Facturación') . '</small></h2>',
      '#weight' => -10,
      '#default_tab' => $config->get('active_tab'),
    ];

    $group = "payment";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Pagos'),
      '#open' => TRUE,
      '#group' => 'bootstrap',
    ];

    $form[$group]['mobile'] = [
      '#type' => 'details',
      '#title' => t('Mobile settings'),
      '#description' => t('Parametros para consumir servicios web para moviles.'),
      '#open' => TRUE,
    ];

    $form[$group]['mobile']['clientId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('TBOL Client Id'),
      '#default_value' => $config->get($group)['mobile']['clientId'],
    ];

    $form[$group]['mobile']['gatewayUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url pasarela'),
      '#default_value' => $config->get($group)['mobile']['gatewayUrl'],
    ];

    $group = 'portfolio';

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Exportar portafolio de servicios'),
      '#open' => FALSE,
      '#group' => 'bootstrap',
    ];

    $form[$group]['download'] = [
      '#type' => 'details',
      '#title' => t('Configuración de exportación de portafolio de servicios'),
      '#description' => t('Configuración general para descargar portafolio de servicios.'),
      '#open' => TRUE,
    ];

    $form[$group]['download']['typeFile'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato descarga'),
      '#options' => [
        'txt' => $this->t('text/plain'),
        'csv' => $this->t('text/csv'),
        'xlsx' => $this->t('application/ms-excel'),
      ],
      '#default_value' => !empty($config->get($group)['download']['typeFile']) ? $config->get($group)['download']['typeFile'] : 'txt' ,
    ];

    $form[$group]['download']['folder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre de la carpeta'),
      '#default_value' => !empty($config->get($group)['download']['folder']) ? $config->get($group)['download']['folder'] : 'Portafolio' ,
      '#description' => t('Nombre de la carpeta donde los archivos seran generados.'),
    ];

    $form[$group]['download']['fileMobile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del archivo movil'),
      '#default_value' => !empty($config->get($group)['download']['fileMobile']) ? $config->get($group)['download']['fileMobile'] : 'Portafolio - movil',
      '#description' => t('Nombre con que se generara el archivo para servicios moviles, este nombre sera complementado con la fecha.'),
    ];

    $form[$group]['download']['fileFixed'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del archivo fijo'),
      '#default_value' => !empty($config->get($group)['download']['fileFixed']) ? $config->get($group)['download']['fileFixed'] : 'Portafolio - fijo',
      '#description' => t('Nombre con que se generara el archivo para servicios fijos, este nombre sera complementado con la fecha.'),
    ];

    $form[$group]['download']['fileZip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del archivo zip'),
      '#default_value' => !empty($config->get($group)['download']['fileZip']) ? $config->get($group)['download']['fileZip'] : 'Portafolio',
      '#description' => t('Nombre con que se generara el archivo zip este nombre sera complementado con la fecha.'),
    ];

    $form[$group]['download']['movileCategory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Categoría móvil'),
      '#default_value' => !empty($config->get($group)['download']['movileCategory']) ? $config->get($group)['download']['movileCategory'] : 'Telefonía móvil',
      '#description' => t('Nombre usado en el campo categoría, en el archivo de servicios moviles.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // Retrieve the configuration.
    $this->config('tbo_billing.bill_payment_settings')
      ->set('payment', $form_state->getValue('payment'))
      ->set('portfolio', $form_state->getValue('portfolio'))
      ->save();

    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
