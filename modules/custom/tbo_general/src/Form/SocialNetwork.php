<?php

namespace Drupal\tbo_general\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Class SocialNetwork.
 *
 * @package Drupal\tbo_general\Form
 */
class SocialNetwork extends ConfigFormBase {
  protected $counter;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_general.social_network',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_network';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // kint($form_state);
    $config = $this->config('tbo_general.social_network');
    $form["#tree"] = TRUE;

    // table_options: fieldset que contiene todas las columnas de la tabla.
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones tabla2'),
      '#open' => TRUE,
    ];
    $form['table_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('label'), t('image'), t('url'), t('Show'), t('Weight'), ''],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
      '#prefix' => '<div class="content-table-fields" id="content-table-fields">',
      '#suffix' => '</div>',
    ];

    // Se ordenan los filtros segun lo establecido en la configuración
    // uasort($table_fields, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));
    // Se crean todas las columnas de la tabla que mostrara la información
    // foreach ($table_fields as $id => $entity) {
    // TableDrag: Mark the table row as draggable.
    $id = 1;
    $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
    // TableDrag: Sort the table row according to its existing/configured weight.
    $form['table_options']['table_fields']['#weight'] = $config->get('weight');

    $form['table_options']['table_fields'][$id]['label'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('label'),
    ];

    $form['table_options']['table_fields'][$id]['image'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('image'),
    ];

    $form['table_options']['table_fields'][$id]['url'] = [
      '#type' => 'textfield',
      '#title' => t('Url de redireccion del logo del sitio'),
      '#default_value' => $config->get('url'),
    ];

    $form['table_options']['table_fields'][$id]['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('show'),
    ];

    // TableDrag: Weight column element.
    $form['table_options']['table_fields'][$id]['weight'] = [
      '#type' => 'weight',
      '#title' => t('Weight for @title', ['@title' => $config->get('label')]),
      '#title_display' => 'invisible',
      '#default_value' => $config->get('weight'),
        // Classify the weight element for #tabledrag.
      '#attributes' => ['class' => ['fields-order-weight']],
    ];

    $form['table_options']['table_fields'][$id]['service_field'] = [
      '#type' => 'hidden',
      '#value' => $config->get('service_field'),
    ];
    // }.
    $form['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('add'),
      '#attributes' => [
        'class' => ['btn-second'],
      ],
      '#ajax' => [
        'callback' => [$this, '_add_fields'],
        'wrapper' => 'content-table-fields',
        'event' => 'click',
      ],
    ];

    // kint($config);
    // kint('hola');
    // return parent::buildForm($form, $form_state);.
    return $form;
  }

  /**
   *
   */
  public function _add_fields(array &$form, FormStateInterface $form_state) {
    // $form_state->set('midata', '10');
    // print_r($form_state->getValue(['table_options', 'table_fields']));
    // exit();
    $id = 12;
    $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
    // TableDrag: Sort the table row according to its existing/configured weight.
    $form['table_options']['table_fields']['#weight'] = 1;

    $form['table_options']['table_fields'][$id]['label'] = [
      '#type' => 'textfield',
      '#default_value' => 12,
    ];

    $form['table_options']['table_fields'][$id]['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Avatar'),
      '#default_value' => '',
      '#description' => $this->t('Por favor ingrese una imagen de formato PNG, JPEG, SVG y medidas minimas 70px X 46px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg svg'],
        'file_validate_image_resolution' => [$maximum_dimensions = 0, $minimum_dimensions = '68x40'],
      ],
    ];

    $form['table_options']['table_fields'][$id]['url'] = [
      '#type' => 'textfield',
      '#title' => t('Url de redireccion del logo del sitio'),
      '#default_value' => 'mi url',
    ];

    $form['table_options']['table_fields'][$id]['show'] = [
      '#type' => 'checkbox',
      '#default_value' => 1,
    ];

    // TableDrag: Weight column element.
    $form['table_options']['table_fields'][$id]['weight'] = [
      '#type' => 'weight',
      '#title' => t('Weight for @title', ['@title' => '12']),
      '#title_display' => 'invisible',
      '#default_value' => 1,
      // Classify the weight element for #tabledrag.
      '#attributes' => ['class' => ['fields-order-weight']],
    ];

    $form['table_options']['table_fields'][$id]['service_field'] = [
      '#type' => 'hidden',
      '#value' => 'test',
    ];

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#content-table-fields', $form['table_options']['table_fields']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // kint($form_state->getValue(['table_options', 'table_fields']));
    // exit();
    $this->config('tbo_general.companyselector')
      ->set('avatar', $form_state->getValue('avatar'))
      ->set('show_avatar', $form_state->getValue('visibility')['show_avatar'])
      ->set('show_name', $form_state->getValue('visibility')['show_name'])
      ->set('show_mail', $form_state->getValue('visibility')['show_mail'])
      ->set('show_button', $form_state->getValue('visibility')['show_button'])
      ->set('url', $form_state->getValue('redirect_button')['url'])
      ->set('label', $form_state->getValue('redirect_button')['label'])
      ->save();

    $fid = $form_state->getValue('avatar');

    // Save file permanently.
    if ($fid) {
      $this->setFileAsPermanent($fid);
    }
  }

  /**
   * Method to save file permanenty in the database.
   *
   * @param string $fid
   *   File id.
   */
  public function setFileAsPermanent($fid) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    $file = File::load($fid);

    // If file doesn't exist return.
    if (!is_object($file)) {
      return;
    }

    // Set as permanent.
    $file->setPermanent();

    // Save file.
    $file->save();

    // Add usage file.
    \Drupal::service('file.usage')->add($file, 'tbo_general', 'tbo_general', 1);
  }

}
