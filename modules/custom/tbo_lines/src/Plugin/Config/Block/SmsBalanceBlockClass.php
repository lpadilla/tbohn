<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_lines\Plugin\Block\SmsBalanceBlock;

class SmsBalanceBlockClass {

	protected $configuration;
	protected $instance;

	/**
	 * @param SmsBalanceBlock $instance
	 * @param $config
	 */
	public function setConfig(SmsBalanceBlock &$instance, &$config) {
		$this->instance = &$instance;
		$this->configuration = &$config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration() {
		return [
			'others_display' => [
				'table_fields' => [
					'in_title'  => [
						'title' => t('Título del bloque'),
						'label' => 'Saldos SMS',
						'show' => TRUE,
					],
				],
			],
			'sms_types' => [
				'table_fields' => [
					'SMS a Tigo' => [
						'title' => t('SMS a Tigo'),
						'label' => 'SMS a Tigo',
						'type' => 'color',
						'color' => '#3764DB',
						'prefix' => 'tigo',
						'show' => TRUE,
					],
					'SMS a todo destino' =>  [
						'title' => t('SMS a todo destino'),
						'label' => 'SMS a todo destino',
						'type' => 'color',
						'color' => '#FBD767',
						'prefix' => 'destiantion',
						'show' => TRUE,
					],
					'SMS a todo operador' => [
						'title' => t('SMS a todo operador'),
						'label' => 'SMS a todo operador',
						'type' => 'color',
						'color' => '#86B84A',
						'prefix' => 'operator',
						'show' => TRUE,
					],
				],
			],
			'buttons' => [
				'table_fields' => [
					'consumptions_history' => [
						'title' => t('Botón historial consumos'),
						'label' => t('historial de consumos'),
						'url' => '/historial-sms',
						'update_label' => TRUE,
						'show' => TRUE,
						'active' => TRUE,
					],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm(&$form, &$form_state) {
		$form = $this->instance->cardBlockForm();

		//table_options: fieldset que contiene todas las columnas de la tabla
		$form['sms_types'] = [
			'#type' => 'details',
			'#title' => t('SMS color\'s'),
			'#open' => TRUE,
		];

		$form['sms_types']['table_fields'] = [
			'#type' => 'table',
			'#header' => [t('Title'), t('Label'),t('Color'), t('Show'),],
			'#empty' => t('There are no items yet. Add an item.'),
		];

		$sms_color = $this->configuration['sms_types']['table_fields'];

		foreach ($sms_color as $key => $value) {

			$form['sms_types']['table_fields'][$key]['title'] = [
				'#plain_text' => $value['title'],
			];

			$form['sms_types']['table_fields'][$key]['label'] = [
				'#type' => 'textfield',
				'#default_value' => $value['label'],
			];

			$form['sms_types']['table_fields'][$key]['color'] = [
				'#type' => $value['type'],
				'#default_value' => $value['color'],
			];

			$form['sms_types']['table_fields'][$key]['show'] = [
				'#type' => 'checkbox',
				'#default_value' => $value['show'],
			];
		}

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit(&$form, FormStateInterface &$form_state, &$configuration) {
		$configuration['sms_types'] = $form_state->getValue('sms_types');
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(SmsBalanceBlock &$instance, &$configuration) {
	  $this->instance = &$instance;
	  $this->configuration = &$configuration;

	  $this->instance->cardBuildHeader(FALSE, FALSE);
	  $this->instance->setValue('directive', 'data-ng-sms-balance');
	  $this->instance->setValue('class', 'block--sms-balance');
	  $this->instance->setValue('config_name','SmsBalanceBlock');

	  $parameters = [
	    'theme' => 'sms_balance',
		  'library' => 'tbo_lines/sms_balance'
		];

	  $others = [
	    '#fields' => $this->configuration['others_display']['table_fields'],
			'#sms' => $this->configuration['sms_types']['table_fields'],
			'#buttons' => $this->configuration['buttons']['table_fields'],
		];

	  $this->instance->cardBuildVarBuild($parameters, $others);

	  $other_configuration = $this->instance->cardBuildConfigBlock('/tbo-lines/rest/sms-balance?_format=json');

	  $this->instance->cardBuildAddConfigDirective($other_configuration);

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

		if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
			return AccessResult::allowed();
		}

		return AccessResult::forbidden();
	}

}
