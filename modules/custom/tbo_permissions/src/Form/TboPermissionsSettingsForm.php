<?php

namespace Drupal\tbo_permissions\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TboPermissionsSettingsForm.
 */
class TboPermissionsSettingsForm extends ConfigFormBase {

  protected $configForm;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->configForm = \Drupal::service('tbo_permissions.tbo_permissions_settings_form_logic');
    $this->configForm->createInstance($this);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_permissions.tbopermissionssettings',
    ];
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
    return $this->configForm->buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (!isset($this->configForm)) {
      $this->configForm = \Drupal::service('tbo_permissions.tbo_permissions_settings_form_logic');
    }
    $this->configForm->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $this->configForm->submitForm($form, $form_state);
  }

}
