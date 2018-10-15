<?php

namespace Drupal\tbo_user_tools\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_user_tools\Form\UserCancelAlterForm;

/**
 * Manage config a 'UserCancelAlterConfigFormClass' block.
 */
class UserCancelAlterConfigFormClass {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state, UserCancelAlterForm &$instance) {

    $instance->setValueInstance('cancelMethods', user_cancel_methods());
    $config = $instance->getValueInstance('config');

    $form = $instance->executeParent('buildForm', $form, $form_state);

    // Remove others options.
    if (isset($form['user_cancel_method']['#options']['user_cancel_block'])) {
      unset($form['user_cancel_method']['#options']['user_cancel_block']);
    }
    if (isset($form['user_cancel_method']['#options']['user_cancel_block_unpublish'])) {
      unset($form['user_cancel_method']['#options']['user_cancel_block_unpublish']);
    }
    if (isset($form['user_cancel_method']['#options']['user_cancel_reassign'])) {
      unset($form['user_cancel_method']['#options']['user_cancel_reassign']);
    }

    // Remove options send confirm message.
    if (isset($form['user_cancel_confirm'])) {
      unset($form['user_cancel_confirm']);
    }

    // Select default value and hidden option.
    $form['user_cancel_method']['#default_value'] = "user_cancel_delete";

    $form['description']['#markup'] = '';

    // Set text Cancel account button.
    $form['actions']['submit']['#value'] = t("Aceptar - Borrar Usuarios");

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state, UserCancelAlterForm &$instance) {
    $user = \Drupal::currentUser();
    $entity = $instance->getValueInstance('entity');
    $logger = \Drupal::service('logger.factory')->get('user');
    // Cancel account immediately, if the current user has administrative
    // privileges, no confirmation mail shall be sent, and the user does not
    // attempt to cancel the own account.
    if (!$form_state->isValueEmpty('access') && $form_state->isValueEmpty('user_cancel_confirm') && $entity->id() != $user->id()) {
      try {
        user_cancel_override($form_state->getValues(), $entity->id(), $form_state->getValue('user_cancel_method'));
      }
      catch (\Exception $e) {
        // Send drupal_set_message.
        drupal_set_message(t('No se pudieron realizar los cambios, por favor intente más tarde'), 'error');
      }

      // Save audit log.
      try {
        $name = $entity->get('full_name')->value;
        if (empty($name)) {
          $name = $entity->getAccountName();
        }
        $this->saveAuditLog($name);
      }
      catch (\Exception $e) {
        // Send message logger.
        \Drupal::logger('delete_user')->error('No se pudo guardar el log de actividad de el usuario @user', ['@user' => $name]);
      }

      $form_state->setRedirectUrl($entity->urlInfo('collection'));
    }
    else {
      // Store cancelling method and whether to notify the user in
      // $this->entity for
      // \Drupal\user\Controller\UserController::confirmCancel().
      $entity->user_cancel_method = $form_state->getValue('user_cancel_method');
      $entity->user_cancel_notify = $form_state->getValue('user_cancel_notify');
      $entity->save();
      _user_mail_notify('cancel_confirm', $entity);
      drupal_set_message(t('A confirmation request to cancel your account has been sent to your email address.'));
      $logger->notice('Sent account cancellation request to %name %email.', ['%name' => $entity->label(), '%email' => '<' . $entity->getEmail() . '>']);

      $form_state->setRedirect(
        'entity.user.canonical',
        ['user' => $entity->id()]
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion($entity) {
    $currentUser = \Drupal::currentUser();
    $email = $entity->getEmail();
    if ($entity->id() == $currentUser->id()) {
      return t('Are you sure you want to cancel your account?');
    }
    return t('Are you sure you want to cancel the account %email?', ['%email' => $email]);
  }

  /**
   * Implements saveAuditLog().
   *
   * @param string $name
   *   The name to audit log.
   */
  public function saveAuditLog($name) {
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    $detalles = t("Usuario @userName borra los siguientes usuarios @usuarios",
      [
        '@userName' => $service->getName(),
        '@usuarios' => $name,
      ]);
    // Create array data[].
    $data = [
      'companyName' => 'No aplica',
      'companyDocument' => 'No aplica',
      'companySegment' => 'No aplica',
      'event_type' => t("Cuenta"),
      'description' => t("Usuario accede a la interfaz y borra usuarios"),
      'details' => $detalles,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $service->insertGenericLog($data);
  }

}
