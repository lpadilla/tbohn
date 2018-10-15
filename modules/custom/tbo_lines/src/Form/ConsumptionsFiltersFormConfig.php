<?php

namespace Drupal\tbo_lines\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConsumptionsFiltersFormConfig
 * @package Drupal\tbo_lines\Form
 */
class ConsumptionsFiltersFormConfig extends ConfigFormBase {
	
	protected $instance;
	
	/**
	 * {@inheritdoc}
	 */
	public function __construct(ConfigFactoryInterface $configFactory) {
		$this->instance = \Drupal::service('tbo_lines.consumptions_filters_form_class');
	  parent::__construct($configFactory);
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
	public function getFormId() {
    return $this->instance->getFormId();
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