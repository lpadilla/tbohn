<?php

namespace Drupal\tbo_general\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Class TboSettingsForm.
 *
 * @package Drupal\tbo_general\Form
 */
class TboSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_general.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_general_form_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tbo_general.settings');

    $form["#tree"] = TRUE;
    $form['bootstrap'] = [
      '#type' => 'vertical_tabs',
      '#prefix' => '<h2><small>' . t('Tbo Settings') . '</small></h2>',
      '#weight' => -10,
      '#default_tab' => $config->get('active_tab'),
    ];

    $group = "region";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Region'),
      '#group' => 'bootstrap',
    ];
    $form[$group]['currency_sign'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Símbolo Moneda'),
      '#maxlength' => 4,
      '#size' => 4,
      '#required' => TRUE,
      '#default_value' => $config->get($group)['currency_sign'],
      '#description' => $this->t('Establece el signo de moneda. Ejemplo: $, Gs, Lb'),
    ];
    $form[$group]['decimal_separator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Separador decimal'),
      '#options' => ['.' => $this->t('Punto (.)'), ',' => $this->t('Coma (,)')],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['decimal_separator'],
      '#description' => $this->t('Establece el signo para separacion decimal.'),
    ];
    $form[$group]['thousand_separator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Separador de miles'),
      '#options' => ['.' => $this->t('Punto (.)'), ',' => $this->t('Coma (,)')],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['thousand_separator'],
      '#description' => $this->t('Establece el signo para separacion de miles.'),
    ];
    $form[$group]['position_currency_sing'] = [
      '#type' => 'radios',
      '#title' => $this->t('Posición simbolo de moneda'),
      '#options' => [
        0 => $this->t('Antes: %sing 1.000', ['%sing' => $config->get($group)['currency_sign']]),
        1 => $this->t('Después: 1.000 %sing', ['%sing' => $config->get($group)['currency_sign']]),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['position_currency_sing'],
      '#description' => $this->t('Establece donde se muestra el signo de moneda.'),
    ];
    $form[$group]['decimal_numbers'] = [
      '#type' => 'select',
      '#title' => $this->t('Decimales'),
      '#options' => [0, 1, 2],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['decimal_numbers'],
      '#description' => $this->t('Número de decimales a mostrar para información de moneda.'),
    ];

    $date_types = DateFormat::loadMultiple();
    $date_formatter = \Drupal::service('date.formatter');
    $hours_format = [];

    foreach ($date_types as $machine_name => $format) {
      if (!strpos($machine_name, 'format_only_our')) {
        $date_formats[$machine_name] = t('@name format', ['@name' => $format->label()]) . ': ' . $date_formatter->format(REQUEST_TIME, $machine_name);
      }
      else {
        $hours_format[$machine_name] = t('@name format', ['@name' => $format->label()]) . ': ' . $date_formatter->format(REQUEST_TIME, $machine_name);
      }
    }

    $form[$group]['format_date'] = [
      '#type' => 'select',
      '#title' => t('Formato de fechas'),
      '#description' => t("Seleccione el formato en que se mostraran las fechas"),
      '#options' => $date_formats,
      '#default_value' => $config->get($group)['format_date'],
    ];

    $form[$group]['format_hour'] = [
      '#type' => 'select',
      '#title' => t('Formato de Horas'),
      '#description' => t("Seleccione el formato en que se mostraran las horas"),
      '#options' => $hours_format,
      '#default_value' => $config->get($group)['format_hour'],
    ];

    $form[$group]['format_units'] = [
      '#type' => 'details',
      '#title' => t('Formato de unidades'),
      '#description' => t('Formato de unidades ejemplo: 1,000, 1,000.00, 1.000, etc.'),
    ];

    $form[$group]['format_units']['decimal_separator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Separador decimal'),
      '#options' => [
        '.' => $this->t('Punto (.)'),
        ',' => $this->t('Coma (,)'),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['format_units']['decimal_separator'],
      '#description' => $this->t('Establece el signo para separacion decimal.'),
    ];

    $form[$group]['format_units']['thousand_separator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Separador de miles'),
      '#options' => [
        '.' => $this->t('Punto (.)'),
        ',' => $this->t('Coma (,)'),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['format_units']['thousand_separator'],
      '#description' => $this->t('Establece el signo para separacion de miles.'),
    ];

    $form[$group]['format_units']['decimal_numbers'] = [
      '#type' => 'select',
      '#title' => $this->t('Decimales'),
      '#options' => [0, 1, 2],
      '#required' => TRUE,
      '#default_value' => $config->get($group)['format_units']['decimal_numbers'],
      '#description' => $this->t('Número de decimales a mostrar para información de unidad.'),
    ];

    // Add format to lines.
    $form[$group]['format_lines'] = [
      '#type' => 'details',
      '#title' => t('Formato de lineas'),
      '#description' => t('Formato de lineas ejemplo: (301) 100 1010 ó 301 100 1010 ó 301-100-1010 ó (301) 100-1010).'),
    ];

    $form[$group]['format_lines']['line_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de linea'),
      '#options' => [
        '0' => 'No aplicar formato',
        '1' => '(301) 100 1010',
        '2' => '301 100 1010',
        '3' => '301-100-1010',
        '4' => '(301) 100-1010',
      ],
      '#required' => FALSE,
      '#default_value' => $config->get($group)['format_lines']['line_format'],
      '#description' => $this->t('Establece el formato para los numeros de lineas del sitio.'),
    ];

    $group = "type_accounts";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Tipos de cuentas'),
      '#group' => 'bootstrap',
    ];

    $form[$group]['home'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Hogar"),
      '#default_value' => isset($config->get($group)['home']) ? $config->get($group)['home'] : 0,
    ];

    $group = "exception_messages";

    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Manejo de excepciones'),
      '#group' => 'bootstrap',
    ];

    $form[$group]['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje a mostrar'),
      '#description' => $this->t('Si no ingresa ningun valor se mostrara el mensaje de la excepción.'),
      '#default_value' => isset($config->get($group)['message']) ? $config->get($group)['message'] : '',
    ];

    $form[$group]['show_service_error'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Mostrar el nombre del servicio que genera el error con el mensaje a mostrar"),
      '#default_value' => isset($config->get($group)['show_service_error']) ? $config->get($group)['show_service_error'] : 0,
    ];

    $form[$group]['show_exception'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Mostrar mensajes de excepciones de los servicios"),
      '#default_value' => isset($config->get($group)['show_exception']) ? $config->get($group)['show_exception'] : 0,
    ];

    $form[$group]['show_exception_only_message'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Mostrar en el mensaje de la excepción unicamente el mensaje de error sin los demas datos de la excepcion que retorna el servicio"),
      '#default_value' => isset($config->get($group)['show_exception_only_message']) ? $config->get($group)['show_exception_only_message'] : 0,
    ];

    // Configuracion de chat.
    $group = "chat";
    $form[$group] = [
      '#type' => 'details',
      '#title' => $this->t('Chat'),
      '#group' => 'bootstrap',
    ];

    $form[$group]['script'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Ingrese la url del script a incluir"),
      '#default_value' => isset($config->get($group)['script']) ? $config->get($group)['script'] : '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getVocabularyOptions() {
    $vids = Vocabulary::loadMultiple();

    $options = [];

    foreach ($vids as $vid) {
      $options[$vid->id()] = $vid->label();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!empty($_FILES['files'])) {
      $file = file_save_upload('import_cities', [
        // Validate extensions.
        'file_validate_extensions' => ['csv'],
      ], "public://", 0);

      if ($file) {
        $form_state->setValue('import_cities_file', $file);
        drupal_set_message(t('File @filepath was uploaded.', ['@filepath' => $file->getFileUri()]));
      }
      elseif ($file === FALSE) {
        drupal_set_message(t('Epic upload FAIL!'), 'error');
      }

    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('tbo_general.settings')
      ->set('region', $form_state->getValue('region'))
      ->set('type_accounts', $form_state->getValue('type_accounts'))
      ->set('exception_messages', $form_state->getValue('exception_messages'))
      ->set('chat', $form_state->getValue('chat'))
      ->save();

    return;
  }

}
