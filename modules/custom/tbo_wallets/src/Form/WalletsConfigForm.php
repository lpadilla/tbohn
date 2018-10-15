<?php

namespace Drupal\tbo_wallets\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class WalletsConfigForm.
 */
class WalletsConfigForm extends ConfigFormBase {
  
  protected $config_form;
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_wallets.walletsconfig',
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_wallets.wallets_config_form_class');
    $this->config_form->createInstance($this);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->config_form->getFormId();
  }
  
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_wallets.walletsconfig');
    $form  =  $this->config_form->buildForm($form, $form_state, $config);
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (!isset($this->config_form)) {
      $this->config_form = \Drupal::service('tbo_wallets.wallets_config_form_class');
    }
    $this->config_form->validateForm($form, $form_state);
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('tbo_wallets.walletsconfig')
      ->set('wallets', $form_state->getValue('wallets'))
      ->save();
  }
}
