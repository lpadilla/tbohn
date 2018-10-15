<?php

namespace Drupal\tbo_mail_bo\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class tboMailBoSettingsForm.
 *
 * @package Drupal\tbo_mail_bo\Form
 */
class tboMailBoSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_mail_bo.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_mail_bo_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_mail_bo.settings');

    $form["#tree"] = TRUE;

    // Block Support Agent.
    $form['support_agent_bo'] = [
      '#type' => 'details',
      '#title' => $this->t('Template para notificar a Agentes de soporte'),
      '#open' => FALSE,
    ];
    $form['support_agent_bo']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#default_value' => $config->get('support_agent_bo')['subject'],
    ];
    $form['support_agent_bo']['body'] = [
      '#type' => 'text_format',
      '#title' => 'Body',
      '#format' => 'full_html',
      '#default_value' => $config->get('support_agent_bo')['body']['value'],
      '#description' => $this->t('You can use tokens.') . render($build),
    ];
    return parent::buildForm($form, $form_state);
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
    $this->config('tbo_mail_bo.settings')      
      ->set('support_agent_bo', $form_state->getValue('support_agent_bo'))
      ->save();
  }
}