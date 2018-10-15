<?php

namespace Drupal\tbo_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ManageEnterpriseForm.
 *
 * @package Drupal\tbo_account\Form
 */
class ManageEnterpriseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_enterprise';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['fieldselect'] = [
      '#type' => 'fieldset',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
