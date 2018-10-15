<?php

namespace Drupal\tbo_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UpdateUserDataForm.
 */
class UpdateUserDataForm extends FormBase {

  /**
   * Config class.
   *
   * @var \Drupal\tbo_account\Plugin\Config\form\UpdateUserInfoFormClass
   */
  private $configForm;

  /**
   * UpdateUserDataForm constructor.
   */
  public function __construct() {
    $this->configForm = \Drupal::service('tbo_account.update_user_info_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_account.update_user_data_form';
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configForm->submitForm($form, $form_state);
  }

}
