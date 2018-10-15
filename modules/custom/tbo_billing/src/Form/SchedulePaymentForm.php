<?php

namespace Drupal\tbo_billing\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SchedulePaymentForm.
 *
 * @package Drupal\tbo_billing\Form
 */
class SchedulePaymentForm extends FormBase {

  private $config_form;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_billing.schedule_payment_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->config_form->getFormId();
  }

  /**
   * {@inheritdoc}
   */

  /**
   * Public function buildForm(array $form, FormStateInterface $form_state, $params = [], $data = []) {.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $data = []) {
    return $this->config_form->buildForm($form, $form_state, $data);
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
    $this->config_form->submitForm($form, $form_state);
  }

}
