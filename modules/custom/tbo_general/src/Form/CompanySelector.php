<?php

namespace Drupal\tbo_general\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CompanySelector.
 *
 * @package Drupal\tbo_general\Form
 */
class CompanySelector extends ConfigFormBase {

  protected $configForm;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_general.companyselector',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->configForm = \Drupal::service('tbo_general.company_selector_form');
    $this->configForm->createInstance($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->configForm->getFormId();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_general.companyselector');
    $form = $this->configForm->buildForm($form, $form_state, $config);
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
    $this->configForm->submitForm($form, $form_state);
    $this->config('tbo_general.companyselector')
      ->set('container', $form_state->getValue('container'))
      ->save();
  }

}
