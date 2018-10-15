<?php

namespace Drupal\tbo_user_tools\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Form\UserMultipleCancelConfirm;

/**
 * Provides a confirmation form for cancelling multiple user accounts.
 */
class UserMultipleCancelConfirmForm extends UserMultipleCancelConfirm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return \Drupal::service('tbo_user_tools.user_multiple_cancel_alter_form')->buildForm($form, $form_state, $this);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return \Drupal::service('tbo_user_tools.user_multiple_cancel_alter_form')->submitForm($form, $form_state, $this);
  }

  /**
   * Implements getValueInstance().
   *
   * @param mixed $value
   *   The value to return.
   *
   * @return mixed
   *   The value.
   */
  public function getValueInstance($value) {
    return $this->$value;
  }

  /**
   * Implements setValueInstance().
   *
   * @param $field
   *   This field.
   * @param $value
   *   This field value.
   */
  public function setValueInstance($field, $value) {
    $this->$field = $value;
  }

  /**
   * Implements executeMethodInstance().
   *
   * @param $method
   *   The method to execute.
   *
   * @return mixed
   *   Execute method.
   */
  public function executeMethodInstance($method) {
    return $method();
  }

  /**
   * Implements executeParent().
   *
   * @param $method
   *   the method.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   Execute parent.
   */
  public function executeParent($method, array &$form, FormStateInterface $form_state) {
    return parent::$method($form, $form_state);
  }

  /**
   * Implements executeTempStoreFactory().
   *
   * @param $current_user_id
   *   The param to execute to tempStoreFactory.
   */
  public function executeTempStoreFactory($current_user_id) {
    // Clear out the accounts from the temp store.
    $this->tempStoreFactory->get('user_user_operations_cancel')->delete($current_user_id);
  }

}
