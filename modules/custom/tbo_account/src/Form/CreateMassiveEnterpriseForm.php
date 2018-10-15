<?php

namespace Drupal\tbo_account\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 *
 */
class CreateMassiveEnterpriseForm extends FormBase {

  private $config_form;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_account.create_massive_form_class');
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
    if (method_exists($this->config_form, 'validateForm')) {
      return $this->config_form->validateForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $this->config_form->submitForm($form, $form_state);
  }

}
