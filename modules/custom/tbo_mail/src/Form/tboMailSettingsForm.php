<?php

namespace Drupal\tbo_mail\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class tboMailSettingsForm.
 *
 * @package Drupal\tbo_mail\Form
 */
class tboMailSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_mail.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_mail_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_mail.settings');

    $form["#tree"] = TRUE;
    $form['social_networks'] = [
      '#type' => 'details',
      '#title' => $this->t('Redes Sociales'),
    ];
    $form['social_networks']['facebook_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook'),
      '#default_value' => $config->get('social_networks')['facebook_url'],
      '#description' => $this->t('Url de facebook'),
    ];
    $form['social_networks']['youtube_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Youtube'),
      '#default_value' => $config->get('social_networks')['youtube_url'],
      '#description' => $this->t('Url de Youtube'),
    ];
    $form['social_networks']['twitter_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter'),
      '#default_value' => $config->get('social_networks')['twitter_url'],
      '#description' => $this->t('Url de Twitter'),
    ];
    $form['social_networks']['instagram_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instagram'),
      '#default_value' => $config->get('social_networks')['instagram_url'],
      '#description' => $this->t('Url Instagram'),
    ];
    $build['help'] = [
      '#theme' => 'token_tree_link',
    // array('tbo_mail'),.
      '#token_types' => 'all',
      '#global_types' => FALSE,
      '#dialog' => TRUE,
    ];

    $form['new_user'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la creación de un nuevo usuario'),
      '#open' => FALSE,
    ];
    $form['new_user']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('new_user')['subject'],
    ];
    $form['new_user']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('new_user')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    $form['new_enterprise'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la creación de una nueva empresa'),
      '#open' => FALSE,
    ];
    $form['new_enterprise']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('new_enterprise')['subject'],
    ];
    $form['new_enterprise']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('new_enterprise')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    $form['assing_enterprise'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la asignación de una empresa a un tigoAdmin'),
      '#open' => FALSE,
    ];
    $form['assing_enterprise']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('assing_enterprise')['subject'],
    ];
    $form['assing_enterprise']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('assing_enterprise')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    $form['config_bill'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la configuración de una factura'),
      '#open' => FALSE,
    ];
    $form['config_bill']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('config_bill')['subject'],
    ];
    $form['config_bill']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('config_bill')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    $form['register_complain'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el recibo de una queja'),
      '#open' => FALSE,
    ];
    $form['register_complain']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('register_complain')['subject'],
    ];
    $form['register_complain']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('register_complain')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    $form['autocreate_account'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la autocreación de una cuenta'),
      '#open' => FALSE,
    ];
    $form['autocreate_account']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('autocreate_account')['subject'],
    ];
    $form['autocreate_account']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('autocreate_account')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Programar pago.
    $form['schedule_payment'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la programacion de un pago con tarjeta de crédito'),
      '#open' => FALSE,
    ];
    $form['schedule_payment']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('schedule_payment')['subject'],
    ];
    $form['schedule_payment']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('schedule_payment')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Eliminar pago programado.
    $form['remove_schedule_payment'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la desprogramación de un pago con tarjeta de crédito'),
      '#open' => FALSE,
    ];
    $form['remove_schedule_payment']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('remove_schedule_payment')['subject'],
    ];
    $form['remove_schedule_payment']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('remove_schedule_payment')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Agregar tarjeta de credito.
    $form['add_card_token'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la adición de una tarjeta de crédito'),
      '#open' => FALSE,
    ];
    $form['add_card_token']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('add_card_token')['subject'],
    ];
    $form['add_card_token']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('add_card_token')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Eliminar tarjeta de credito.
    $form['remove_card_token'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la eliminación de una tarjeta de crédito'),
      '#open' => FALSE,
    ];
    $form['remove_card_token']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('remove_card_token')['subject'],
    ];
    $form['remove_card_token']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('remove_card_token')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Asignación de empresa notificacion super admin.
    $form['assing_enterprise_super_admin'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la asignación de empresa a un super admin'),
      '#open' => FALSE,
    ];
    $form['assing_enterprise_super_admin']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('assing_enterprise_super_admin')['subject'],
    ];
    $form['assing_enterprise_super_admin']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('assing_enterprise_super_admin')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Asignación de usuario a empresa existente en autocreación.
    $form['assing_enterprise_old'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la asignación de un usuario a una empresa existente'),
      '#open' => FALSE,
    ];
    $form['assing_enterprise_old']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('assing_enterprise_old')['subject'],
    ];
    $form['assing_enterprise_old']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('assing_enterprise_old')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Change wifi password.
    $form['change_wifi_pass'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el cambio de contraseña de WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_pass']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_pass')['subject'],
    ];
    $form['change_wifi_pass']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('change_wifi_pass')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Change wifi net name.
    $form['change_wifi_net_name'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el cambio del nombre de la red WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_net_name']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_net_name')['subject'],
    ];
    $form['change_wifi_net_name']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('change_wifi_net_name')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Change wifi dmz.
    $form['change_wifi_dmz'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el cambio del dmz de la red WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_dmz']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_dmz')['subject'],
    ];
    $form['change_wifi_dmz']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('change_wifi_dmz')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Notificación pago de multiples facturas.
    $form['multiple_invoices_payment'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el pago de múltiples facturas'),
      '#open' => FALSE,
    ];
    $form['multiple_invoices_payment']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('multiple_invoices_payment')['subject'],
    ];
    $form['multiple_invoices_payment']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('multiple_invoices_payment')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Change Sim Card.
    $form['change_sim_card'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el cambio de SIM Card'),
      '#open' => FALSE,
    ];
    $form['change_sim_card']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_sim_card')['subject'],
    ];
    $form['change_sim_card']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('change_sim_card')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Change wifi network channel.
    $form['change_wifi_channel'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el cambio de canal de la red de WiFi'),
      '#open' => FALSE,
    ];
    $form['change_wifi_channel']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_wifi_channel')['subject'],
    ];
    $form['change_wifi_channel']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('change_wifi_channel')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Change Security Type.
    $form['change_security_type'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el cambio del Tipo de Seguridad WiFi'),
      '#open' => FALSE,
    ];
    $form['change_security_type']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('change_security_type')['subject'],
    ];
    $form['change_security_type']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('change_security_type')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    // Daily cards access modifications Excel report.
    $form['daily_cards_access_modifications_excel_report'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el reporte diario de Cambios Transaccionales'),
      '#open' => FALSE,
    ];
    $form['daily_cards_access_modifications_excel_report']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('daily_cards_access_modifications_excel_report')['subject'],
    ];
    $form['daily_cards_access_modifications_excel_report']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('daily_cards_access_modifications_excel_report')['body']['value'],
      '#description' => $this->t('You can use tokens.') . ' ' . render($build),
    ];

    // Block Sim Card.
    $form['block_sim_card'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar el bloqueo de SIM Card'),
      '#open' => FALSE,
    ];
    $form['block_sim_card']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('block_sim_card')['subject'],
    ];
    $form['block_sim_card']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('block_sim_card')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];

    $form['download_contract'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar Descarga de contratos'),
      '#open' => FALSE,
    ];
    $form['download_contract']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('download_contract')['subject'],
    ];
    $form['download_contract']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('download_contract')['body']['value'],
      '#description' => $this->t('You can use tokens.') . ' ' . render($build),
    ];

    $form['update_user_info'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar la actualización de datos personales'),
      '#open' => FALSE,
    ];
    $form['update_user_info']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('update_user_info')['subject'],
    ];
    $form['update_user_info']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('update_user_info')['body']['value'],
      '#description' => $this->t('You can use tokens.') . ' ' . render($build),
    ];

    $form['images'] = [
      '#type' => 'details',
      '#title' => $this->t('Imagenes'),
      '#open' => TRUE,
    ];
    $form['images']['logo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Logo'),
      '#default_value' => $config->get('images')['logo'],
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
      ],
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

    // Obtenemos el id del archivo subido.
    $fid = $form_state->getValue('images')['logo'];

    // Verificamos si viene un id de archivo para el logo
    // y lo seteamos como permanente para evitar ser borrado.
    if ($fid) {
      $this->setPermanentFile($fid);
    }

    // Grabamos configuracion.
    $this->config('tbo_mail.settings')
      ->set('social_networks', $form_state->getValue('social_networks'))
      ->set('new_user', $form_state->getValue('new_user'))
      ->set('new_enterprise', $form_state->getValue('new_enterprise'))
      ->set('assing_enterprise', $form_state->getValue('assing_enterprise'))
      ->set('config_bill', $form_state->getValue('config_bill'))
      ->set('register_complain', $form_state->getValue('register_complain'))
      ->set('autocreate_account', $form_state->getValue('autocreate_account'))
      ->set('schedule_payment', $form_state->getValue('schedule_payment'))
      ->set('remove_schedule_payment', $form_state->getValue('remove_schedule_payment'))
      ->set('add_card_token', $form_state->getValue('add_card_token'))
      ->set('remove_card_token', $form_state->getValue('remove_card_token'))
      ->set('assing_enterprise_super_admin', $form_state->getValue('assing_enterprise_super_admin'))
      ->set('assing_enterprise_old', $form_state->getValue('assing_enterprise_old'))
      ->set('change_wifi_pass', $form_state->getValue('change_wifi_pass'))
      ->set('change_wifi_net_name', $form_state->getValue('change_wifi_net_name'))
      ->set('change_wifi_dmz', $form_state->getValue('change_wifi_dmz'))
      ->set('multiple_invoices_payment', $form_state->getValue('multiple_invoices_payment'))
      ->set('change_sim_card', $form_state->getValue('change_sim_card'))
      ->set('change_wifi_channel', $form_state->getValue('change_wifi_channel'))
      ->set('change_security_type', $form_state->getValue('change_security_type'))
      ->set('block_sim_card', $form_state->getValue('block_sim_card'))
      ->set('daily_cards_access_modifications_excel_report', $form_state->getValue('daily_cards_access_modifications_excel_report'))
      ->set('download_contract', $form_state->getValue('download_contract'))
      ->set('update_user_info', $form_state->getValue('update_user_info'))
      ->set('images', $form_state->getValue('images'))
      ->save();
  }

  /**
   * Hacemos permanente el archivo cargado.
   *
   * @param mixed $fid
   *   Fid.
   */
  private function setPermanentFile($fid) {

    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    // Colocamos el archivo como permanente para evitar ser borrado por el cron.
    $file = File::load($fid);

    // Ignoramos si no se obtiene un objeto.
    if (!is_object($file)) {
      return;
    }

    // Seteamos como permanente.
    $file->setPermanent();

    // Salvamos.
    $file->save();

    // Add usage file.
    \Drupal::service('file.usage')->add($file, 'tbo_mail', 'tbo_mail', 1);
  }

}
