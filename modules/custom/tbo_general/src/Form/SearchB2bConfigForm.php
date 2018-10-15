<?php

namespace Drupal\tbo_general\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SearchB2bConfigForm.
 *
 * @package Drupal\tbo_general\Form
 */
class SearchB2bConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_general.search_b2b_config_form',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_b2b_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_general.search_b2b_config_form');

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#description' => $this->t('Ingrese la url para enviar la busquedad, el token {data} contiene lo ingresado por el usuario en el campo de busqueda'),
      '#maxlength' => 250,
      '#size' => 250,
      '#default_value' => $config->get('url'),
      '#required' => TRUE,
    ];

    $form['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PlaceHolder'),
      '#description' => $this->t('Ingrese el placeholder del campo busqueda'),
      '#maxlength' => 250,
      '#size' => 250,
      '#default_value' => $config->get('placeholder'),
      '#required' => TRUE,
    ];

    $form['new_tag'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Nueva pestaña'),
      '#description' => $this->t('Abrir la busqueda en una nueva pestaña'),
      '#default_value' => $config->get('new_tag'),
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

    $this->config('tbo_general.search_b2b_config_form')
      ->set('url', $form_state->getValue('url'))
      ->set('placeholder', $form_state->getValue('placeholder'))
      ->set('new_tag', $form_state->getValue('new_tag'))
      ->save();

    // Delete cache render.
    $delete = 'block:radix_tbo_search';
    $query = \Drupal::database()->delete('cache_render');
    $query->condition('cid', '%' . $delete . '%', 'LIKE');
    $query->execute();
  }

}
