<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_services\Plugin\Block\ReturnBlock;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;

class ReturnBlockClass {
	
	protected $instance;
	protected $configuration;
	
	/**
	 * {@inheritdoc}
	 */
	public function setConfig(ReturnBlock &$instance, &$configuration) {
		$this->instance = $instance;
		$this->configuration = $configuration;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration() {
	  return [
	  	'table_options' => [
	      'table_fields' => [
	    	  'icon' => [
	    	    'title' => t('Icono'),
					  'show' => 1,
					  'weight' => 1,
					  'service_field' => 'return_icon',
				  ],
				  'text' => [
				    'title' => t('Texto'),
					  'label' => '',
					  'show' => 1,
					  'weight' => 2,
					  'service_field' => 'return_text',
				  ],
			  ],
			],
			'others' => [
				'config' => [
					'show_margin' => [
						'show_margin_card' => FALSE,
					],
				],
			],
			'url' => '#',
			'block_options' => [
				'block_border' => TRUE,
				'block_without_border' => FALSE,
				'block_transparent_movil' => FALSE,
			],
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function blockForm() {
	  $form = $this->instance->cardBlockForm();
	  
	  $form['url'] = [
	  	'#type' => 'textfield',
			'#title' => t('url'),
			'#required' => TRUE,
			'#default_value' => $this->configuration['url'],
		];
	  
	  $form['block_options'] = [
	  	'#type' => 'container',
		];
	  
	  $form['block_options']['block_border'] = [
	  	'#title' => 'Bloque con borde',
			'#type' => 'checkbox',
			'#default_value' => $this->configuration['block_options']['block_border'],
		];
		
		$form['block_options']['block_without_border'] = [
			'#title' => 'Bloque sin borde borde',
			'#type' => 'checkbox',
			'#default_value' => $this->configuration['block_options']['block_without_border'],
		];
		
		$form['block_options']['block_transparent_movil'] = [
			'#title' => 'Bloque sin fondo para movil',
			'#type' => 'checkbox',
			'#default_value' => $this->configuration['block_options']['block_transparent_movil'],
		];
	  
	  return $form;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state, &$configuration) {
		$configuration['url'] = $form_state->getValue('url');
		$configuration['block_options'] = $form_state->getValue('block_options'); // Save new values
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function build(ReturnBlock &$instance, &$configuration) {
		
		$this->instance = &$instance;
		$this->configuration = &$configuration;
		
		$this->instance->setValue('class', 'block-return');
		
		$params = [
			'theme' => 'return_block',
			'library' => 'tbo_services/return_block'
		];
		
		$margin = ($this->configuration['others']['config']['show_margin']['show_margin_card'] == 1) ? 1 : 0;
		
		$others = [
		  '#fields' => $this->configuration['table_options']['table_fields'],
			'#url' => $this->configuration['url'],
			'#margin' => [
			  'show_margin_card' => $margin,
				'other' => $this->configuration['others']['config'],
			],
			'#block_options' => $this->configuration['block_options'],
		];
		
		$this->instance->cardBuildVarBuild($params, $others);
		
		$build = $this->instance->getValue('build');
		
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