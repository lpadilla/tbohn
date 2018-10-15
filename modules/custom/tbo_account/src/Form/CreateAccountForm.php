<?php

namespace Drupal\tbo_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CreateAccountForm.
 *
 * @package Drupal\tbo_account\Form
 */
class CreateAccountForm extends FormBase {

  private $config_form;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_account.create_account_form');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    return $this->config_form->buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    return $this->config_form->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitCancel(array &$form, FormStateInterface $form_state) {
    return $this->config_form->submitCancel($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config_form->submitForm($form, $form_state);
    return;
  }

}
