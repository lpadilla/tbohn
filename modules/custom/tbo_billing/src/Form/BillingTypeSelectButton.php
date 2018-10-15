<?php

namespace Drupal\tbo_billing\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BillingTypeSelectButton.
 *
 * @package Drupal\tbo_billing\Form
 */
class BillingTypeSelectButton extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billing_type_select_button';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = 'movil';

    if ($_GET) {
      if ($_GET['billing_type'] == 'movil') {
        $config = 'fijo';
      }
      else {
        $config = 'movil';
      }
      $form['billing_type'] = [
        '#type' => 'hidden',
        '#value' => $config,
      ];

      $form['send'] = [
        '#type' => 'submit',
        '#value' => $config,
      ];
    }
    else {
      if ($config == 'movil') {
        $config = 'fijo';
      }
      else {
        $config = 'movil';
      }

      $form['billing_type'] = [
        '#type' => 'hidden',
        '#value' => $config,
      ];

      $form['send'] = [
        '#type' => 'submit',
        '#value' => $config,
      ];
    }

    $form['#method'] = 'GET';
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
