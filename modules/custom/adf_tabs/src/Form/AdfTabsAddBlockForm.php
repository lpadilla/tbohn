<?php

namespace Drupal\adf_tabs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding a block plugin to a variant.
 */
class AdfTabsAddBlockForm extends FormBase {

  /**
   * @var
   */
  protected $config_form;

  /**
   * AdfTabsAddBlockForm constructor.
   */
  public function __construct() {
    $this->config_form = \Drupal::service('adf_tabs.adf_tabs_add_block_form');
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
  protected function prepareBlock($plugin_id, $config = []) {
    if (!empty($config)) {
      $block = $this->config_form->createInstance($plugin_id, $config);
    }
    else {
      $block = $this->config_form->createInstance($plugin_id);
    }

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $block_display = NULL, $block_id = NULL, $row_id = NULL) {
    return $this->config_form->buildForm($form, $form_state, $block_display, $block_id, $row_id);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (!isset($this->config_form)) {
      $this->config_form = \Drupal::service('adf_tabs.adf_tabs_add_block_form');
    }
    $this->config_form->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $this->config_form->submitForm($form, $form_state);
  }

}
