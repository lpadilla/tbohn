<?php

namespace Drupal\tbo_atp\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;

class TboAtpFormConfigClass {

  /**
   * {@inheritdoc
   */
  public function getEditableConfigNames() {
    return [
      'tbo_atp.config',
    ];
  }

  /**
   * {@inheritdoc
   */
  public function getFormId() {
    return 'tbo_atp_config';
  }

  /**
   * {@inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = \Drupal::config('tbo_atp.config');
    $charges = $config->get('atp_translate')['charges'];

		$form["#tree"] = TRUE;
		$form['bootstrap'] = [
			'#type' => 'vertical_tabs',
			'#prefix' => '<h2><small>' . t('Tbo Settings') . '</small></h2>',
			'#weight' => -10,
			'#default_tab' => 'atp_translate',
		];

    $group = 'atp_translate';

		$form[$group] = [
			'#type' => 'details',
			'#title' => t('Traducción de cargos'),
			'#group' => 'bootstrap',
		];

		$form[$group]['charges'] = [
			'#type' => 'table',
			'#header' => [t('Field'), t('Label'),],
			'#empty' => t('There are no items yet. Add an item.'),
		];

		if(!empty($charges)) {
			foreach ($charges as $key => $value) {

				$form[$group]['charges'][$key]['base']['title'] = [
					'#plain_text' => $value['base']['title'],
				];

				$form[$group]['charges'][$key]['translation']['label'] = [
					'#type' => 'textfield',
					'#required' => TRUE,
					'#default_value' => $config->get('atp_translate')['charges'][$key]['translation']['label'],
				];
			}
		}

		$group = 'atp_config';

		$atp_config = $config->get('atp_config');

		$form[$group] = [
			'#type' => 'details',
			'#title' => t('Configuración de rutas'),
			'#group' => 'bootstrap',
		];

		$form[$group]['url_redirect'] = [
			'#type' => 'textfield',
			'#title' => t('Url de re-dirección'),
			'#required' => 1,
			'#default_value' => (!isset($atp_config['url_redirect'])) ? '/' : $atp_config['url_redirect'],
			'#description' => t('URL externa: https://example.com   - URL interna: /example'),
		];

		$form[$group]['type_redirect'] = [
			"#type" => 'radios',
			'#title' => t('tipo de re-direción'),
		  '#options' => [
		  	'internal' => t('URL interna'),
				'external' => t('URL externa'),
			],
			'#default_value' => (!isset($atp_config['type_redirect'])) ? 'internal' : $atp_config['type_redirect']
		];

		$form[$group]['atp_url'] = [
			'#type' => 'textfield',
			'#title' => t('Ruta arma tu plan business'),
			'#required' => 1,
			'#default_value' => (!isset($atp_config['atp_url'])) ? '/arma-tu-plan' : $atp_config['atp_url'],
		];

    return $form;
  }

  /**
   * {@inheritdoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('tbo_atp.config');
    $charges = $config->get('atp_translate')['charges'];
    $new_values = $form_state->getValue('atp_translate')['charges'];

    foreach($form_state->getValue('atp_translate')['charges'] as $key => $value) {
      $save[$key]['base']['title'] = $charges[$key]['base']['title'];
      $save[$key]['translation']['label'] = $new_values[$key]['translation']['label'];
    }

    $save_data['charges'] = $save;

    $config->set('atp_translate', $save_data)
			->set('atp_config', $form_state->getValue('atp_config'))
      ->save();
  }

}
