<?php

namespace Drupal\adf_import_data\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdfImportDataFormConfig.
 *
 * @package Drupal\adf_import_data\Form
 */
class AdfImportDataFormConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adf_import_data.import_config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adf_import_data_form_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('adf_import_data.import_config');

    $form['cant_element'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cantidad de elementos'),
      '#description' => $this->t('Cantidad de elementos por página'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('cant_element'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $elements = $form_state->getValue('cant_element');
    $cache = $form_state->getValue('cache_time');
    $element = '';

    if (empty($elements)) {
      $form_state->setError($form['cant_element'], 'Debe definir la cantidad de elementos');
    }
    elseif (!is_numeric($elements) && $elements < 1) {
      $form_state->setError($form['cant_element'], 'La cantidad debe ser numérica');
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('adf_import_data.import_config')
      ->set('cant_element', $form_state->getValue('cant_element'))
      ->save();
  }

}
