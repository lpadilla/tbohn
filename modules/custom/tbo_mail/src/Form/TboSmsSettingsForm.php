<?php

namespace Drupal\tbo_mail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TboSmsSettingsForm.
 */
class TboSmsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_mail.tbosmssettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_sms_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_mail.tbosmssettings');

    $form["#tree"] = TRUE;

    // Codigo para envio de sms.
    $form['sms_code'] = [
      '#type' => 'textfield',
      '#title' => t('Configurar codigo de envio'),
      '#default_value' => $config->get('sms_code'),
      '#required' => TRUE,
    ];

    // Autocreacion de cuenta.
    $form['autocreate'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS autocreación de cuenta'),
      '#open' => FALSE,
    ];
    $form['autocreate']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('autocreate')['subject'],
    ];
    $form['autocreate']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      // '#format' => 'full_html',.
      '#default_value' => $config->get('autocreate')['body'],
     // '#description' => $this->t('You can use tokens. '). render($build),
    ];

    $form['new_enterprise'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS empresa nueva'),
      '#open' => FALSE,
    ];
    $form['new_enterprise']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('new_enterprise')['subject'],
    ];
    $form['new_enterprise']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('new_enterprise')['body'],
    ];

    $form['new_user'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS usuario nuevo'),
      '#open' => FALSE,
    ];
    $form['new_user']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('new_user')['subject'],
    ];
    $form['new_user']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('new_user')['body'],
    ];

    $form['change_wifi_pass'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS Cambio de contraseña WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_pass']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_pass')['subject'],
    ];
    $form['change_wifi_pass']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('change_wifi_pass')['body'],
    ];

    // Change SIM Card.
    $form['change_sim_card'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS Cambio de SIM Card'),
      '#open' => FALSE,
    ];
    $form['change_sim_card']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_sim_card')['subject'],
    ];
    $form['change_sim_card']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('change_sim_card')['body'],
    ];

    // Change wifi net name.
    $form['change_wifi_net_name'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS Cambio de nombre de la red WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_net_name']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_net_name')['subject'],
    ];
    $form['change_wifi_net_name']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('change_wifi_net_name')['body'],
    ];

    // Change wifi dmz.
    $form['change_wifi_dmz'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS para notificar el cambio en la configuración DMZ'),
      '#open' => FALSE,
    ];
    $form['change_wifi_dmz']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_dmz')['subject'],
    ];
    $form['change_wifi_dmz']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('change_wifi_dmz')['body'],
    ];

    // Change Wifi network channel.
    $form['change_wifi_channel'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS Cambio de canal de la red WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_channel']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_channel')['subject'],
    ];
    $form['change_wifi_channel']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('change_wifi_channel')['body'],
    ];

    // Change Security Type.
    $form['change_security_type'] = [
      '#type' => 'details',
      '#title' => $this->t('SMS Cambio de Tipo de Seguridad WiFi'),
      '#open' => FALSE,
    ];
    $form['change_security_type']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_security_type')['subject'],
    ];
    $form['change_security_type']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Body',
      '#default_value' => $config->get('change_security_type')['body'],
    ];

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

    $this->config('tbo_mail.tbosmssettings')
      ->set('sms_code', $form_state->getValue('sms_code'))
      ->set('autocreate', $form_state->getValue('autocreate'))
      ->set('new_enterprise', $form_state->getValue('new_enterprise'))
      ->set('new_user', $form_state->getValue('new_user'))
      ->set('change_wifi_pass', $form_state->getValue('change_wifi_pass'))
      ->set('change_sim_card', $form_state->getValue('change_sim_card'))
      ->set('change_security_type', $form_state->getValue('change_security_type'))
      ->set('change_wifi_net_name', $form_state->getValue('change_wifi_net_name'))
      ->set('change_wifi_dmz', $form_state->getValue('change_wifi_dmz'))
      ->set('change_wifi_channel', $form_state->getValue('change_wifi_channel'))
      ->save();
  }

}
