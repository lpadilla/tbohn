<?php

namespace Drupal\tbo_services\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ConfigVendorChangeSimFormClass {

  /**
   * {@inheritdoc.
   */
  public function getEditableConfigNames() {
    return [
      'tbo_services.config_vendor_change_sim',
    ];
  }

  /**
   * {@inheritdoc.
   */
  public function getFormId() {
    return 'config_vendor_change_sim_form';
  }

  /**
   * {@inheritdoc.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = \Drupal::config('tbo_services.config_vendor_change_sim');
    // $form = [];.
    $form['id_vendor'] = [
      '#type' => 'textfield',
      '#title' => t('Configurar ID del vendedor'),
      '#required' => TRUE,
      '#default_value' => $config->get('id_vendor'),
    ];

    $form['type_vendor'] = [
      '#type' => 'textfield',
      '#title' => t('Configurar tipo de documento del vendedor'),
      '#required' => TRUE,
      '#default_value' => $config->get('type_vendor'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('tbo_services.config_vendor_change_sim');

    $config->set('id_vendor', $form_state->getValue('id_vendor'))
      ->set('type_vendor', $form_state->getValue('type_vendor'))
      ->save();
  }

}
