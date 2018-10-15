<?php

namespace Drupal\tbo_account\Plugin\Config;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_account\Plugin\Block\AutocreateAccountBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;

/**
 * Manage config a 'AutocreateAccountBlockClass' block.
 */
class AutocreateAccountBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * @param \Drupal\tbo_account\Plugin\Block\AutocreateAccountBlock $instance
   * @param $config
   */
  public function setConfig(AutocreateAccountBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'AutocreateAccountBlock');
    $this->instance->setValue('class', 'block-autocreate-account');
    $this->instance->setValue('id', 'block-card');

    $request = \Drupal::service('tbo_account.create_account');
    $form = $request->getCreateAccountForm();

    $build = [
      '#theme' => 'autocreate_account',
      '#uuid' => $this->instance->getValue('uuid'),
      '#class' => $this->instance->getValue('class'),
      '#id' => $this->instance->getValue('id'),
      '#form' => $form,
      '#plugin_id' => $this->instance->getPluginId(),
    ];

    // Build.
    $this->instance->setValue('build', $build);
    // Return build.
    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('authenticated', $roles) && count($roles) <= 1) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration;

    $entity = Node::load($config['link_terminos_node']);
    $form['link_terminos_node'] = [
      '#title' => t('Pagina de términos y condiciones'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $entity,
      '#description' => t('Puede escoger una página básica que contenga los términos y condiciones'),
      '#maxlength' => 256,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_terminos_node'] = $form_state->getValue('link_terminos_node');
  }

}
