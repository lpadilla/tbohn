<?php

namespace Drupal\tbo_atp\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TboAtpFormConfig.
 *
 * @package Drupal\tbo_segment\Form
 */
class TboAtpFormConfig extends ConfigFormBase {

  //class with form logic
  protected $instance;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->instance = \Drupal::service('tbo_atp.config_form_logic');
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
