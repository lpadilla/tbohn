<?php

namespace Drupal\tbo_general\Plugin\Config\Block;

use Drupal\tbo_general\Plugin\Block\LogoCopySiteBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\user\PrivateTempStoreFactory;
/**
 * Manage config a 'LogoCopySiteBlockClass' block.
 */

class LogoCopySiteBlockClass {
  protected $configuration;
  protected $instance;

  /**
   * @param \Drupal\tbo_general\Plugin\Block\LogoCopySiteBlock $instance
   * @param $config
   */
  public function setConfig(LogoCopySiteBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [],
      ],
      'others' => [
        'config' => [
          'image' => '',
          'url_logo' => '',
          'copyrigth' => 'copyrigth',
        ]
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm( array &$form, FormStateInterface &$form_state) {
    $form['others'] = [
      '#type' => 'details',
      '#title' => t('CONFIGURACIÓN LOGO Y COPYRIGTH'),
      '#description' => t('Campos para el logo y copyrigth'),
      '#open' => TRUE,
    ];
    $form['others']['config']['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Logo'),
      '#default_value' => $this->configuration['others']['config']['image'],
      '#description' => t('Por favor ingrese una imagen de formato PNG, JPEG y medidas minimas 175 px X 23 px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_image_resolution' => array("175x23"),
        'file_validate_extensions' => ['png jpg svg'],
      ],
    ];
    $form['others']['config']['url_logo'] = [
      '#type' => 'url',
      '#title' => t('Url logo'),
      '#description' => t('Ingrese la URL para el logo'),
      '#default_value' => $this->configuration['others']['config']['url_logo'],
    ];   
    $form['others']['config']['copyrigth'] = [
      '#type' => 'textfield',
      '#title' => t('Copyrigth'),
      '#description' => t('Ingrese Copyrigth para el footer'),
      '#default_value' => $this->configuration['others']['config']['copyrigth'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface &$form_state, &$config) {
    $logo_copy = $form_state->getValue('others')['config'];
    foreach ($logo_copy as $key => $value) {
      $this->configuration['others']['config'][$key] = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(LogoCopySiteBlock &$instance, &$config) {
 
    $this->instance = &$instance;
    $this->configuration = &$config;
    
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $build = [];

    $this->instance->setValue('config_name', 'FixedLogoCopySiteBlock');
    $this->instance->setValue('directive', 'data-ng-fixed-logo-copy-site');
    $this->instance->setValue('class', 'block--fixed-logo-copy-site');
    $this->instance->ordering('table_options');
    
    $config_data = array(
      "image_src" => "", 
      "url_logo" => $this->configuration['others']['config']['url_logo'], 
      "copyrigth" => $this->configuration['others']['config']['copyrigth']
    );
    $file = File::load(reset($this->configuration['others']['config']['image']));
    if ($file) {
      $src = file_create_url($file->getFileUri());
      $config_data['image_src'] = $src;
    }
    
    $build = array(
      '#theme' => 'logo_copy_site_block',
      '#uuid' => $this->instance->getValue('uuid'),
      '#config' => $config_data,
    );
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
