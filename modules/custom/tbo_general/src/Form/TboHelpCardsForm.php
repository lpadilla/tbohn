<?php

namespace Drupal\tbo_general\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TboHelpCardsForm.
 *
 * @package Drupal\tbo_general\Form
 */
class TboHelpCardsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_general.settings.help_card',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_general_settings_help_card';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_general.settings.help_card');

    $form["#tree"] = TRUE;
    $form['bootstrap'] = [
      '#type' => 'vertical_tabs',
      '#prefix' => '<h2><small>' . t('Tbo Help Cards') . '</small></h2>',
      '#weight' => -10,
      '#default_tab' => $config->get('active_tab'),
    ];

    $group = "contract_number";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Field - Contract Number'),
      '#group' => 'bootstrap',
      '#description' => $this->t('Form - Create Account'),
    ];

    // FIJO.
    $form[$group]['fixed'] = [
      '#type' => 'details',
      '#title' => $this->t('Fijo'),
      '#open' => TRUE,
    ];
    // Show the thumbnail preview.
    $default_value = '';
    $default_caption = '';
    if ($config->get($group)) {
      $config_ = $config->get($group);
      $default_value = $config_['fixed']['image'];
      $default_caption = $config_['fixed']['caption'];
    }
    $form[$group]['fixed']['image'] = [
    // You can find a list of available types in the form api.
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#default_value' => $default_value,
      '#upload_location' => 'public://help_cards',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '232x292', $minimum_dimensions = '50x50'],
      ],
      '#description' => $this->t('El icono debe medir entre 232x292 pixeles y 50x50 pixeles, de extension png jpg jpeg'),
    ];
    $form[$group]['fixed']['caption'] = [
    // You can find a list of available types in the form api.
      '#type' => 'textfield',
      '#title' => $this->t('Descripción de la imagen'),
      '#default_value' => $default_caption,
    ];

    // MOVIL.
    $form[$group]['mobile'] = [
      '#type' => 'details',
      '#title' => $this->t('Movil'),
      '#open' => TRUE,
    ];
    // Show the thumbnail preview.
    $default_value = '';
    $default_caption = '';
    if ($config->get($group)) {
      $config_ = $config->get($group);
      $default_value = $config_['mobile']['image'];
      $default_caption = $config_['mobile']['caption'];
    }
    $form[$group]['mobile']['image'] = [
    // You can find a list of available types in the form api.
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#default_value' => $default_value,
      '#upload_location' => 'public://help_cards',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '232x292', $minimum_dimensions = '50x50'],
      ],
      '#description' => $this->t('El icono debe medir entre 232x292 pixeles y 50x50 pixeles, de extension png jpg jpeg'),
    ];
    $form[$group]['mobile']['caption'] = [
    // You can find a list of available types in the form api.
      '#type' => 'textfield',
      '#title' => $this->t('Descripción de la imagen'),
      '#default_value' => $default_caption,
    ];

    $group = "referent_payment";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Field - Referent Payment'),
      '#group' => 'bootstrap',
      '#description' => $this->t('Form - Create Account'),
    ];

    // FIJO.
    $form[$group]['fixed'] = [
      '#type' => 'details',
      '#title' => $this->t('Fijo'),
      '#open' => TRUE,
    ];
    // Show the thumbnail preview.
    $default_value = '';
    $default_caption = '';
    if ($config->get($group)) {
      $config_ = $config->get($group);
      $default_value = $config_['fixed']['image'];
      $default_caption = $config_['fixed']['caption'];
    }
    $form[$group]['fixed']['image'] = [
    // You can find a list of available types in the form api.
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#default_value' => $default_value,
      '#upload_location' => 'public://help_cards',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '232x292', $minimum_dimensions = '50x50'],
      ],
      '#description' => $this->t('El icono debe medir entre 232x292 pixeles y 50x50 pixeles, de extension png jpg jpeg'),
    ];
    $form[$group]['fixed']['caption'] = [
    // You can find a list of available types in the form api.
      '#type' => 'textfield',
      '#title' => $this->t('Descripción de la imagen'),
      '#default_value' => $default_caption,
    ];

    // MOVIL.
    $form[$group]['mobile'] = [
      '#type' => 'details',
      '#title' => $this->t('Movil'),
      '#open' => TRUE,
    ];
    // Show the thumbnail preview.
    $default_value = '';
    $default_caption = '';
    if ($config->get($group)) {
      $config_ = $config->get($group);
      $default_value = $config_['mobile']['image'];
      $default_caption = $config_['mobile']['caption'];
    }
    $form[$group]['mobile']['image'] = [
    // You can find a list of available types in the form api.
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#default_value' => $default_value,
      '#upload_location' => 'public://help_cards',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => [$maximum_dimensions = '232x292', $minimum_dimensions = '50x50'],
      ],
      '#description' => $this->t('El icono debe medir entre 232x292 pixeles y 50x50 pixeles, de extension png jpg jpeg'),
    ];
    $form[$group]['mobile']['caption'] = [
    // You can find a list of available types in the form api.
      '#type' => 'textfield',
      '#title' => $this->t('Descripción de la imagen'),
      '#default_value' => $default_caption,
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

    $this->config('tbo_general.settings.help_card')
      ->set('contract_number', $form_state->getValue('contract_number'))
      ->set('referent_payment', $form_state->getValue('referent_payment'))
      ->save();

    $fid = $form_state->getValue('contract_number')['fixed']['image'];
    // Save file permanently.
    if ($fid) {
      \Drupal::service('tbo_general.tbo_config')->setFileAsPermanent($fid);
    }

    $fid = $form_state->getValue('contract_number')['mobile']['image'];
    // Save file permanently.
    if ($fid) {
      \Drupal::service('tbo_general.tbo_config')->setFileAsPermanent($fid);
    }

    $fid = $form_state->getValue('referent_payment')['fixed']['image'];
    // Save file permanently.
    if ($fid) {
      \Drupal::service('tbo_general.tbo_config')->setFileAsPermanent($fid);
    }

    $fid = $form_state->getValue('referent_payment')['mobile']['image'];
    // Save file permanently.
    if ($fid) {
      \Drupal::service('tbo_general.tbo_config')->setFileAsPermanent($fid);
    }

    return;
  }

}
