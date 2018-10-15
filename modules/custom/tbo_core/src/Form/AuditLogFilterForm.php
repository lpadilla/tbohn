<?php

namespace Drupal\tbo_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AuditLogFilterForm.
 *
 * @package Drupal\tbo_core\Form
 */
class AuditLogFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'audit_log_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // $form['#action'] = 'internal:/api/logs';
    /*
    $form['#attributes'] = array(
    'ng-submit' => 'submit()',
    );
     */

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha'),
      '#attributes' => [
        // 'placeholder' => 'Desde',.
        'class' => "datepicker",
      ],
      '#maxlength' => 64,
      '#size' => 64,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#maxlength' => 64,
      '#attributes' => [
        'placeholder' => 'Hasta',
        'class' => "datepicker",
      ],
      '#size' => 64,
    ];

    $form['company_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empresa/Cliente:'),
      '#maxlength' => 200,
      '#size' => 200,
    ];

    $form['company_segment'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Segmento:'),
      '#maxlength' => 300,
      '#size' => 300,
    ];

    $form['user_names'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombres:'),
      '#maxlength' => 300,
      '#size' => 300,
    ];

    $form['user_role'] = [
      '#type' => 'checkboxes',
      '#options' => ['SAT' => t('SAT'), 'ACT' => t('ACT')],
      '#title' => $this->t('Typo'),
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empresa/Cliente:'),
      '#maxlength' => 300,
      '#size' => 300,
    ];

    $form['details'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Detalle'),
      '#maxlength' => 350,
      '#size' => 350,
    ];

    $form['old_values'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Anterior'),
      '#maxlength' => 130,
      '#size' => 130,
    ];

    $form['new_values'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nuevo'),
      '#maxlength' => 130,
      '#size' => 130,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Aplicar'),
    ];

    return $form;
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
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
