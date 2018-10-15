<?php

namespace Drupal\tigoid\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tigoid\Form\SettingsForm;

/**
 * Class SettingsForm config.
 */
class SettingsFormClass {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tigoid_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance(SettingsForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config) {
    $form['country_code'] = [
      '#type' => 'textfield',
      '#title' => t('Country code'),
      '#size' => 4,
      '#maxlength' => 2,
      '#default_value' => $config->get('country_code'),
      '#description' => t('When a line has HE, his country code will be compare with this value to allow silent-login'),
    ];
    $form['indicative'] = [
      '#type' => 'textfield',
      '#title' => t("Indicative's phone"),
      '#size' => 4,
      '#maxlength' => 3,
      '#default_value' => $config->get('indicative'),
    ];
    return $form;
  }

}
