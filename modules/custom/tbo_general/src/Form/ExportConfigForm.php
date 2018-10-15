<?php

namespace Drupal\tbo_general\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExportConfigForm.
 *
 * @package Drupal\tbo_general\Form
 */
class ExportConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'tbo_general.export_config',
    ];
  }

  /**
   *
   */
  public function getFormId() {
    return 'export_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $configuration = $this->config('tbo_general.export_config');
    $form = [];
    $form["#tree"] = TRUE;

    $form['date_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuración formato de hora'),
    ];

    $form['date_config']['date_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Formato de hora'),
      '#options' => [
        'long' => 'long default',
        'short' => 'short default',
        'medium' => 'medium default',
        'fallback' => 'Fallback date format',
      ],
      '#default_value' => $configuration->get('date_options'),
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

    $this->config('tbo_general.export_config')
      ->set('date_options', $form_state->getValue('date_config')['date_options'])
      ->save();
  }

}
