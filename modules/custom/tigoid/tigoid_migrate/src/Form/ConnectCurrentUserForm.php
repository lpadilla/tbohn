<?php

namespace Drupal\tigoid_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConnectCurrentUserForm.
 *
 * @package Drupal\tigoid_migrate\Form
 */
class ConnectCurrentUserForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tigoid_migrate_connect_current_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['openid_connect_client_tigoid_connect'] = [
      '#type' => 'submit',
      '#value' => t('Conectar a TigoID'),
      '#name' => 'connect__tigoid',
    ];

    $form['openid_connect_client_tigoid_connect']['#attributes']['class'][] = 'primary';

    return $form;
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

    $client_name = "tigoid";

    $pluginManager = \Drupal::service('plugin.manager.openid_connect_client.processor');

    $configuration = $this->config('openid_connect.settings.' . $client_name)
      ->get('settings');
    $client = $pluginManager->createInstance(
      $client_name,
      $configuration
    );

    $user = \Drupal::currentUser();

    $_SESSION['openid_connect_op'] = "connect";
    $_SESSION['openid_connect_connect_uid'] = $user->id();
    $response = $client->authorize('openid email profile', $form_state);
    $form_state->setResponse($response);
  }

}
