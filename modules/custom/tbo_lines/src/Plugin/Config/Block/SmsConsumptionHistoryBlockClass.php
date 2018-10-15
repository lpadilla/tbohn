<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_lines\Plugin\Block\SmsConsumptionHistoryBlock;

class SmsConsumptionHistoryBlockClass {

	protected $instance;
	protected $config;

	/**
	 * @param SmsConsumptionHistoryBlock $instance
	 * @param $config
	 */
	public function setConfig(SmsConsumptionHistoryBlock &$instance, &$config) {
		$this->instance = &$instance;
		$this->config = &$config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration() {
		return [
			'filters_options' => [
				'filters_fields' => [
					'start_date_sms' => [
						'title' => t("Desde"),
						'label' => 'Desde',
						'service_field' => 'start_date_sms',
						'show' => 1, 'weight' => 1,
						'class' => '5-columns',
						'identifier' => 'start_date_sms',
						'date_line' => 1,
					],
					'end_date_sms' => [
						'title' => t("Hasta"),
						'label' => 'Hasta',
						'service_field' => 'end_date_sms',
						'show' => 1, 'weight' => 2,
						'class' => '5-columns',
						'identifier' => 'end_date_sms',
						'date_line' => 1,
					],
				],
	    ],
		  'table_options' => [
		  	'table_fields' => [
		  		'date' => [
		  		  'title' => t('Fecha'),
						'label' => 'Fecha',
						'service_field' => 'date_show',
						'weight' => 1,
						'show' => TRUE,
						'class' => '4-columns',
					],
					'hour' => [
						'title' => t('Hora'),
						'label' => 'Hora',
						'service_field' => 'hour',
						'weight' => 2,
						'show' => TRUE,
						'class' => '4-columns',
					],
					'msisdn' => [
						'title' => t('Destino'),
						'label' => 'Destino',
						'service_field' => 'msisdn',
						'weight' => 3,
						'show' => TRUE,
						'class' => '4-columns',
					],
				],
			],
			'table_mobile' => [
        'table_fields' => [
		  		'date' => [
		  		  'title' => t('Fecha'),
						'label' => 'Fecha',
						'service_field' => 'date_show',
						'weight' => 1,
						'show' => TRUE,
						'class' => '4-columns',
					],
					'hour' => [
						'title' => t('Hora'),
						'label' => 'Hora',
						'service_field' => 'hour',
						'weight' => 2,
						'show' => TRUE,
					  'class' => '4-columns',
					],
					'msisdn' => [
						'title' => t('Destino'),
						'label' => 'Destino',
						'service_field' => 'msisdn',
						'weight' => 3,
						'show' => TRUE,
					  'class' => '4-columns',
				  ],
        ],
			],
			'others' => [
				'config' => [
				  'paginate' => [
						'number_rows_pages' => 10,
					],
					'show_margin' => [
						'show_margin_card' => FALSE,
					],
				],
			],
			'others_display' => [
				'table_fields' => [
				  'text_info' => [
				  	'title' => t('Texto informativo'),
						'label' => 'Los datos presentados son una referencia de consumo. Pueden variar dependiendo de la hora de generación del reporte',
						'service_field' => 'text_info',
						'show' => TRUE,
					],
					'download_button' => [
						'title' => t('botón descargar'),
						'label' => 'Descargar reporte',
						'service_field' => 'download_button',
						'show' => TRUE,
					],
				],
			],
			'buttons' => [
				'table_fields' =>[
					'view_button' => [
						'title' => t('Botón "ver"  de filtros'),
						'label' => 'Ver',
						'service_field' => 'view_button',
						'show' => TRUE,
						'update_label' => TRUE,
					]
				],
			],
			'empty_message' => 'No hay información disponible para las fechas seleccionadas. Por favor intenta con un rango de fechas diferentes.',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm(&$form, &$form_state) {
	  $form = $this->instance->cardBlockForm();

		$form['buttons']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'),];

		unset($form['buttons']['table_fields']['view_button']['url'], $form['buttons']['table_fields']['view_button']['active']);

		$form['others_display']['#title'] = t('Configuraciones reporte consumo SMS');

		$form['others_display']['table_fields']['#header'] = [t('Title'), t('Label'), t('Show'),];

		$form['table_mobile'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones tabla movile'),
      '#open' => TRUE,
    ];

    $form['table_mobile']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Title'), t('Label'), t('Show'), t('Weight'), t('Espaciado'), ''],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];
		$form['table_mobile']['info'] = [
			'#plain_text' => t('Al seleccionar los campos fecha y hora se mostrarán en una sola columna.'),
		];

    $fields_movile = $this->config['table_mobile']['table_fields'];

    //Se ordenan los filtros segun lo establecido en la configuración
    uasort($fields_movile, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    //Se crean todas las columnas de la tabla que mostrara la información
    foreach ($fields_movile as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['table_mobile']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table_mobile']['table_fields']['#weight'] = $entity['weight'];
      // Some table columns containing raw markup.
      $form['table_mobile']['table_fields'][$id]['title'] = [
         '#plain_text' => $entity['title'],
      ];

      // Some table columns containing raw markup.
      if (isset($entity['label'])) {
        $form['table_mobile']['table_fields'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        ];
      }
      else {
        $form['table_mobile']['table_fields'][$id]['label'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['table_mobile']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // TableDrag: Weight column element.
      $form['table_mobile']['table_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      if (isset($entity['class'])) {
        $form['table_mobile']['table_fields'][$id]['class'] = [
          '#type' => 'select',
          '#options' => [
            '' => t('Ninguno'),
            'destacado' => t('Destacado'),
            '1-columns' => t('Una columna'),
            '2-columns' => t('Dos columnas'),
            '3-columns' => t('Tres columnas'),
            '4-columns' => t('Cuatro columnas'),
            '5-columns' => t('Cinco columnas'),
            '6-columns' => t('Seis columnas'),
            '7-columns' => t('Siete columnas'),
            '8-columns' => t('Ocho columnas'),
            '9-columns' => t('Nueve columnas'),
            '10-columns' => t('Diez columnas'),
            '11-columns' => t('Once columnas'),
            '12-columns' => t('Doce columnas'),
          ],
          '#default_value' => $entity['class'],
        ];
      }
      else {
        $form['table_mobile']['table_fields'][$id]['class'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['table_mobile']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    $form['empty_message'] = [
    	'#type' => 'textfield',
			'#title' => t('Mensaje cuando la tabla no tiene información'),
			'#default_value' => $this->config['empty_message'],
		];

		return $form;

	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state, &$config) {
	  $config['table_mobile'] = $form_state->getValue('table_mobile');
	  $config['empty_message'] = $form_state->getValue('empty_message');
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(SmsConsumptionHistoryBlock &$instance, &$config) {

		$this->instance = &$instance;
		$this->config = &$config;

		$this->instance->cardBuildHeader(FALSE, TRUE);
		$this->instance->setValue('class', 'block--sms-consumption-history');
		$this->instance->setValue('directive', 'data-ng-sms-consumption-history');
		$this->instance->setValue('config_name', 'smsConsumptionHistoryBlock');

		//Ordering table_fields
		$this->instance->ordering('filters_fields', 'filters_options');

		//Set filters configurations
		$filters = [];

		foreach ($this->instance->getValue('filters_fields') as $key_filter => $filter) {
			if ($filter['show'] == 1) {

				$filters[$key_filter]['identifier'] = $key_filter;
				$filters[$key_filter]['label'] = $filter['label'];
				$classes = ["field-" . $filter['service_field'], $filter['class']];
				$filters[$key_filter]['class'] = implode(" ", $classes);
				$filters[$key_filter]['service_field'] = $filter['service_field'];
				$filters[$key_filter]['show'] = $filter['show'];
				$filters[$key_filter]['date_line'] = $filter['date_line'];

				if (!empty($filter['validate_length'])) {
					$filters[$key_filter]['validate_length'] = $filter['validate_length'];
				}

				if ($key_filter == 'user_role') {
					$filters[$key_filter]['select_multiple'] = TRUE;
				}
			}
		}

		//Set filters
		$this->instance->setValue('filters', $filters);

    //Set filters movile
    $filters_mobile = $this->config['table_mobile']['table_fields'];

		if($filters_mobile['msisdn']['show'] == TRUE) {
			$fields_mobile['msisdn'] = [
				'show' => $filters_mobile['msisdn']['show'],
				'label' => $filters_mobile['msisdn']['label'],
				'class' => $filters_mobile['msisdn']['class'],
				'service_field' => $filters_mobile['msisdn']['service_field'],
				'weight' => $filters_mobile['msisdn']['weight'],
			];
		}

		if($filters_mobile['date']['show'] == TRUE && $filters_mobile['hour']['show'] == TRUE) {
			$fields_mobile['both'] = [
    		'show' => TRUE,
				'label' => t('@date y @hour', ['@date' => $filters_mobile['date']['label'], '@hour' => $filters_mobile['hour']['label']]),
				'class' => 'field-both 6-columns',
				'service_field' => 'both',
				'weight' => ($filters_mobile['date']['weight'] >= $filters_mobile['hour']['weight']) ? $filters_mobile['date']['weight'] : $filters_mobile['hour']['weight'],
			];

			$fields_mobile['msisdn']['class'] = '6-columns';
		}
		elseif($filters_mobile['date']['show'] == TRUE && $filters_mobile['hour']['show'] != TRUE) {
			$fields_mobile['date'] = [
				'show' => $filters_mobile['date']['show'],
				'label' => $filters_mobile['date']['label'],
				'class' => $filters_mobile['date']['class'],
				'service_field' => $filters_mobile['date']['service_field'],
				'weight' => $filters_mobile['date']['weight'],
			];
		}
		elseif($filters_mobile['date']['show'] != TRUE && $filters_mobile['hour']['show'] == TRUE) {
			$fields_mobile['hour'] = [
				'show' => $filters_mobile['hour']['show'],
				'label' => $filters_mobile['hour']['label'],
				'class' => $filters_mobile['hour']['class'],
				'service_field' => $filters_mobile['hour']['service_field'],
				'weight' => $filters_mobile['hour']['weight'],
			];
		}

		$this->instance->cardSortArray($fields_mobile);

		$fil_conf = \Drupal::config('tbo_lines.consumptions_filters');
		$days = $fil_conf->get('days_query');

		$parameters = [
		  'theme' => 'sms_consumption_history',
			'library' => 'tbo_lines/sms_consumption_history',
		];

		$others = [
			'#report' => $this->config['others_display']['table_fields'],
			'#show_report' => $this->config['others_display']['show_report'],
			'#margin' => $this->config['others']['config']['show_margin'],
			'#filters' => $this->instance->getValue('filters'),
			'#buttons' => $this->config['buttons']['table_fields'],
			'#fields_mobile' => $fields_mobile,
			'#title' => [
				'label' => $this->config['label'],
				'label_display' => $this->config['label_display'],
			],
		];

		$this->instance->cardBuildVarBuild($parameters, $others);

		$twig = \Drupal::service('twig');
		$text_info = t("El rango de fechas no debe ser superior a @days días", ['@days'=> $days]);
		$twig->addGlobal('informative_text_filters_lines', $text_info);

		$other_data = [
		  'days_query' => $fil_conf->get('days_query'),
			'month_query' => $fil_conf->get('month_query'),
			'init_day' => $fil_conf->get('init_day'),
			'end_day' => $fil_conf->get('end_day'),
			'empty_message' => $this->config['empty_message'],
		];

		$others_params = $this->instance->cardBuildConfigBlock('/tbo_lines/rest/sms-consumption-history?_format=json', $other_data);
		$this->instance->cardBuildAddConfigDirective($others_params);

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
