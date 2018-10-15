<?php

namespace Drupal\tbo_general_co\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MobilePaymentUrlForm.
 */
class MobilePaymentUrlForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_general_co.mobilepaymenturl',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mobile_payment_url_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_general_co.mobilepaymenturl');
    $form['payment_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url de pago'),
      '#description' => $this->t('Url para pagos de servicios móviles en Colombia'),
      '#default_value' => $config->get('payment_url'),
    ];
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ClientId'),
      '#description' => $this->t('Client Id para realizar pago'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('client_id'),
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

    $this->config('tbo_general_co.mobilepaymenturl')
      ->set('payment_url', $form_state->getValue('payment_url'))
      ->set('client_id', $form_state->getValue('client_id'))
      ->save();
  }

}
