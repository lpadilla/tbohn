<?php

namespace Drupal\tbo_services\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigVendorChangeSimForm.
 *
 * @package Drupal\tbo_services\Form
 */
class ConfigVendorChangeSimForm extends ConfigFormBase {

  /**
   * Class with form logic.
   */
  protected $instance;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->instance = \Drupal::service('tbo_services.change_sim_config');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return $this->instance->getEditableConfigNames();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->instance->getFormId();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = $this->instance->buildForm($form, $form_state);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->instance->validateForm($form, $form_state);
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->instance->submitForm($form, $form_state);
  }

}
