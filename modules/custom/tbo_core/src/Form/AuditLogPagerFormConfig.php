<?php

namespace Drupal\tbo_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AuditLogPagerFormConfig.
 *
 * @package Drupal\tbo_core\Form
 */
class AuditLogPagerFormConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_core.auditlogpagerformconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'audit_log_pager_form_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_core.auditlogpagerformconfig');
    $form['pages'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Páginas'),
      '#description' => $this->t('Cantidad de páginas'),
      '#maxlength' => 10,
      '#size' => 64,
      '#default_value' => $config->get('pages'),
      '#required' => TRUE,
    ];
    $form['page_elements'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Elementos por página'),
      '#description' => $this->t('Cantidad de elementos por página'),
      '#maxlength' => 10,
      '#size' => 64,
      '#default_value' => $config->get('page_elements'),
      '#required' => TRUE,
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

    $this->config('tbo_core.auditlogpagerformconfig')
      ->set('pages', $form_state->getValue('pages'))
      ->set('page_elements', $form_state->getValue('page_elements'))
      ->save();
  }

}
