<?php

namespace Drupal\tbo_lines\Plugin\Config\Form;

/**
 * Class ConsumptionsFiltersFormConfigClass
 * @package Drupal\tbo_lines\Plugin\Config\Form
 */
class ConsumptionsFiltersFormConfigClass {
	
	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
	  return 'tbo_lines.tbo_lines_consumptions_filters_config';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getEditableConfigNames() {
	  return [
	  	'tbo_lines.consumptions_filters',
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function buildForm($form, $form_state) {
		
		$config = \Drupal::config('tbo_lines.consumptions_filters');
		
	  $form['days_query'] = [
	    '#type' => 'number',
			'#title' => t('Total de días por consulta'),
			'#description' => t('Número de días que se pueden consultar, valor entre 0 y 60.'),
			'#attributes' => [
				'max'=> ['60'],
				'min' => ['1'],
			],
			'#default_value' => ($config->get('days_query')) ? $config->get('days_query') : 0,
		];
	  
	  $form['month_query'] = [
			'#type' => 'number',
			'#title' => t('Total de meses por consulta'),
			'#description' => t('Número de días que se pueden consultar, valor entre 0 y 12.'),
			'#attributes' => [
				'max'=> ['12'],
				'min' => ['0'],
			],
			'#default_value' => ($config->get('month_query')) ? $config->get('month_query') : 0,
		];
	  
	  $form['init_day'] = [
			'#type' => 'number',
			'#title' => t('Día inicio de consulta'),
			'#description' => t('Día del mes donde se permite el de la inicio de consulta, valor entre 0 y 31.'),
			'#attributes' => [
				'max'=> ['31'],
				'min' => ['0'],
			],
			'#default_value' => ($config->get('init_day')) ? $config->get('init_day') : 0,
		];
	  
	  $form['end_day'] = [
			'#type' => 'number',
			'#title' => t('Día del hasta donde se permite consultar'),
			'#description' => t('Día del mes hasta donde se permite consultar, valor entre 0 y 31.'),
			'#attributes' => [
				'max'=> ['31'],
				'min' => ['0'],
			],
			'#default_value' => ($config->get('end_day')) ? $config->get('end_day') : 0,
		];
	  
	  return $form;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function validateForm(&$form, $form_state) {
		if($form_state->getValue('days_query') < 0 || $form_state->getValue('month_query') < 0 || $form_state->getValue('init_day') < 0 || $form_state->getValue('end_day') < 0) {
			drupal_set_message(t('El valor no puede ser menor que 0'),'error');
			$form_state->setError($form, 'El valor no puede ser menor que 0');
		}
		
		if($form_state->getValue('end_day') < $form_state->getValue('init_day')  && $form_state->getValue('init_day') != 0) {
			$form_state->setError($form['end_day'], t('El valor del día limite de consulta no puede ser menor al día inicial de consulta (@day)',['@day' => $form_state->getValue('init_day')]));
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function submitForm(&$form, $form_state) {
	  $config = \Drupal::service('config.factory')->getEditable('tbo_lines.consumptions_filters');
	  
    $config->set('days_query', $form_state->getValue('days_query'))
			->set('month_query', $form_state->getValue('month_query'))
			->set('init_day', $form_state->getValue('init_day'))
			->set('end_day', $form_state->getValue('end_day'))
			->save();
	}

}