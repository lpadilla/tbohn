<?php

namespace Drupal\tigoid\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\tigoid\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\tigoid\Plugin\Config\Form\SettingsFormClass
   */
  protected $configForm;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tigoid.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->configForm = \Drupal::service('tigoid.settings_config_form');
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
    $config = $this->config('tigoid.settings');
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

    $this->config('tigoid.settings')
      ->set('country_code', $form_state->getValue('country_code'))
      ->set('indicative', $form_state->getValue('indicative'))
      ->save();
  }

}
