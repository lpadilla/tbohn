<?php

namespace Drupal\tbo_emulate_bo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * ModalForm class.
 */
class ModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_form_example_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['#prefix'] = '<div id="modal_form_add_new_user" class="row">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['new_user_status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    // Add fields form
    $form['new_user_document_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Document Type'),
      '#options' => [1,2,3,4],
      '#required' => TRUE,
      '#prefix' => '<div class="col s6">',
      '#suffix' => '</div>',
    ];

    $form['new_user_document_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Document Number'),
      '#required' => TRUE,
      '#prefix' => '<div class="input-field col s6">',
      '#suffix' => '</div>',
    ];

    $form['new_user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Names'),
      '#required' => TRUE,
      '#prefix' => '<div class="input-field col s6">',
      '#suffix' => '</div>',
    ];

    $form['new_user_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#prefix' => '<div class="input-field col s6">',
      '#suffix' => '</div>',
    ];

    $form['new_user_rol'] = [
      '#type' => 'select',
      '#title' => $this->t('Role'),
      '#options' => ['role 1', 'role 2', 'role 3'],
      '#required' => TRUE,
      '#prefix' => '<div class="col s6">',
      '#suffix' => '</div>',
    ];

    $form['new_user_msisdn'] = [
      '#type' => 'select',
      '#title' => $this->t('MSISD'),
      '#options' => [1,2,3,4],
      '#required' => TRUE,
      '#prefix' => '<div class="input-field col s6">',
      '#suffix' => '</div>',
    ];

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create new user'),
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attributes']['class'][] = 'col s12';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Success!", 'The modal form has been submitted.', ['width' => 800]));
    }

    return $response;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   * An array of configuration object names that are editable if called in
   * conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() { return ['config.modal_form_example_modal_form']; }
}

