<?php

namespace Drupal\tbo_account_hn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_account\Form\CreateEnterpriseForm;

/**
 * Class CreateCompanyForm.
 *
 * @package Drupal\tbo_account_hn\Form
 */
class CreateCompanyForm extends CreateEnterpriseForm {

  protected $config_form;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_account_hn.create_companies_form');
    $this->config_form->createInstance($this);
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
    if (!isset($this->config_form)) {
      $this->config_form = \Drupal::service('tbo_account_hn.create_companies_form');
    }
    $this->config_form->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $this->config_form->submitForm($form, $form_state);
  }
}