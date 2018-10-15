<?php

namespace Drupal\adf_rest_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdfRestApiSettingsForm.
 *
 * @package Drupal\adf_rest_api\Form
 */
class AdfRestApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adf_rest_api.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adf_rest_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client id'),
      // '#default_value' => variable_get('tigoapiservices_rest_default_client_id', ''),.
      '#default_value' => $this->config('adf_rest_api.settings')->get('client_id'),
      '#required' => TRUE,
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client secret'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('client_secret'),
      '#required' => TRUE,
    ];

    $form['environment_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment prefix'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('environment_prefix'),
      '#size' => 4,
      '#description' => $this->t('Prefijo de ambiente a utilizar (Ejemplo: prod or test). Este valor se sobre-escribira en el subdominio del endpoint'),
      '#required' => TRUE,
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout expiration'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('timeout'),
      '#description' => $this->t('Maximum number of seconds to waiting a response.'),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => 60,
    ];
    $form['country_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country code'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('country_code'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => $this->t('Country code (e.g. CO, PY, SV, BO, HN, GT...)'),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => 60,
    ];
    $form['prefix_country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prefix Country'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('prefix_country'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => $this->t('Prefix for the number line (e.g. 503, 57,...)'),
      '#min' => 1,
      '#max' => 100,
    ];
    $form['debug_cache'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug cache'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('debug_cache'),
      '#description' => $this->t('Almacena en el informe de errores (watchdog) el cacheo de servicios'),
    ];
    $form['segment_track_exception'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enviar excepciones a segment'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('segment_track_exception'),
      '#description' => $this->t('Enviar las excepciones de los servicios a segment'),
    ];
    $form['segment_track_exception_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Codigo de excepciones que no se deben enviar a segment'),
      '#default_value' => $this->config('adf_rest_api.settings')->get('segment_track_exception_code'),
      '#description' => $this->t("Ingrese los codigos de las excepciones que no se deben enviar a segment, separadas por ',' sin espacios ejemplo: 404,403,400"),
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

    $this->config('adf_rest_api.settings')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->set('environment_prefix', $form_state->getValue('environment_prefix'))
      ->set('timeout', $form_state->getValue('timeout'))
      ->set('debug_cache', $form_state->getValue('debug_cache'))
      ->set('country_code', $form_state->getValue('country_code'))
      ->set('prefix_country', $form_state->getValue('prefix_country'))
      ->set('segment_track_exception', $form_state->getValue('segment_track_exception'))
      ->set('segment_track_exception_code', $form_state->getValue('segment_track_exception_code'))
      ->save();

    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
