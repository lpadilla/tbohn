<?php

namespace Drupal\tbo_user_tools\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_user_tools\Form\UserMultipleCancelConfirmForm;

/**
 * Manage config a 'UserMultipleCancelConfigFormClass' block.
 */
class UserMultipleCancelConfigFormClass {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state, UserMultipleCancelConfirmForm &$instance) {

    $instance->setValueInstance('cancelMethods', user_cancel_methods());
    $config = $instance->getValueInstance('config');

    $form = $instance->executeParent('buildForm', $form, $form_state);

    if (!is_object($form)) {
      // Remove others options.
      if (isset($form['user_cancel_method'])) {
        if (isset($form['user_cancel_method']['#options']['user_cancel_block'])) {
          unset($form['user_cancel_method']['#options']['user_cancel_block']);
        }
        if (isset($form['user_cancel_method']['#options']['user_cancel_block_unpublish'])) {
          unset($form['user_cancel_method']['#options']['user_cancel_block_unpublish']);
        }
        if (isset($form['user_cancel_method']['#options']['user_cancel_reassign'])) {
          unset($form['user_cancel_method']['#options']['user_cancel_reassign']);
        }

        // Select default value and hidden option.
        $form['user_cancel_method']['#default_value'] = "user_cancel_delete";
      }

      // Remove options send confirm message.
      if (isset($form['user_cancel_confirm'])) {
        unset($form['user_cancel_confirm']);
      }

      if (isset($form['description'])) {
        $form['description']['#markup'] = '';
      }

      if (isset($form['actions'])) {
        // Set text Cancel account button.
        $form['actions']['submit']['#value'] = t("Aceptar - Borrar Usuarios");
      }

      // Override list.
      $tempStoreFactory = $instance->getValueInstance('tempStoreFactory');
      $accounts = $tempStoreFactory
        ->get('user_user_operations_cancel')
        ->get(\Drupal::currentUser()->id());

      $names = [];
      foreach ($accounts as $account) {
        $uid = $account->id();
        $name = $account->get('full_name')->value;
        if (empty($name)) {
          $name = $account->getAccountName();
        }
        $names[$uid] = $name;
      }

      if (isset($form['account'])) {
        $form['account']['names']['#items'] = $names;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state, UserMultipleCancelConfirmForm &$instance) {
    $user = \Drupal::currentUser();
    $current_user_id = $user->id();
    $userStorage = $instance->getValueInstance('userStorage');
    $entityManager = $instance->getValueInstance('entityManager');

    // Clear out the accounts from the temp store.
    $instance->executeTempStoreFactory($current_user_id);
    if ($form_state->getValue('confirm')) {
      $users = [];
      foreach ($form_state->getValue('accounts') as $uid => $value) {
        // Prevent programmatic form submissions from cancelling user 1.
        if ($uid <= 1) {
          continue;
        }
        // Prevent user administrators from deleting themselves
        // without confirmation.
        if ($uid == $current_user_id) {
          $admin_form_mock = [];
          $admin_form_state = $form_state;
          $admin_form_state->unsetValue('user_cancel_confirm');
          // The $user global is not a complete user entity, so load the full
          // entity.
          $account = $userStorage->load($uid);
          $admin_form = $entityManager->getFormObject('user', 'cancel');
          $admin_form->setEntity($account);
          // Calling this directly required to init form object with $account.
          $admin_form->buildForm($admin_form_mock, $admin_form_state);
          $admin_form->submitForm($admin_form_mock, $admin_form_state);
        }
        else {
          $account = $userStorage->load($uid);
          $name = $account->get('full_name')->value;
          if (empty($name)) {
            $name = $account->getAccountName();
          }
          array_push($users, $name);
          try {
            user_cancel_override($form_state->getValues(), $uid, $form_state->getValue('user_cancel_method'));
          }
          catch (\Exception $e) {
            // Send drupal_set_message.
            drupal_set_message(t('No se pudieron realizar los cambios, por favor intente más tarde'), 'error');
          }
        }
      }

      // Save audit log.
      if (!empty($users)) {
        try {
          $this->saveAuditLog($users);
        }
        catch (\Exception $e) {
          // Send message logger.
          $users = implode(', ', $users);
          \Drupal::logger('delete_user')->error("No se pudo guardar el log de actividad de los usuarios @users", ['@users' => $users]);
        }
      }
    }

    $form_state->setRedirect('entity.user.collection');
  }

  /**
   * Implements saveAuditLog().
   *
   * @param $users
   *   The users to audit log.
   */
  public function saveAuditLog($users) {
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    $users = implode(', ', $users);
    $detalles = t("Usuario @userName borra los siguientes usuarios @usuarios",
      [
        '@userName' => $service->getName(),
        '@usuarios' => $users,
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
