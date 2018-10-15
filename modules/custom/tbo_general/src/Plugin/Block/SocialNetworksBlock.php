<?php

namespace Drupal\tbo_general\Plugin\Block;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_general\CardBlockBase;

/**
 * Provides a 'SocialNetworksBlock' block.
 *
 * @Block(
 *  id = "social_networks_block",
 *  admin_label = @Translation("Redes sociales"),
 * )
 */
class SocialNetworksBlock extends CardBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_fields' => [],
      'table_fields' => [
        'twitter' => [
          'label' => $this->t('Twitter'),
          'image' => '',
          'url' => '',
          'show' => 1,
          'weight' => 1,
          'service_field' => 'twitter',
        ],
        /*'facebook' => [
          'label' => $this->t('Facebook'),
          'image' => '', 'url' => '', 'show' => 1, 'weight' => 1, 'service_field' => 'facebook',
        ],
        'instagram' => [
          'label' => $this->t('Instagram'),
          'image' => '', 'url' => '', 'show' => 1, 'weight' => 1, 'service_field' => 'instagram',
        ],*/
      ],
      'others_display' => [],
      'buttons' => [],
      'others' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // $table_fields: variable que contiene la configuracion por defecto de las columnas de la tabla.
    $table_fields = $this->configuration['table_fields'];

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
    ];

    // Se ordenan los filtros segun lo establecido en la configuración.
    uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    // Se crean todas las columnas de la tabla que mostrara la información.
    foreach ($table_fields as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table_options']['table_fields']['#weight'] = $entity['weight'];

      $form['table_options']['table_fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      ];

      $form['table_options']['table_fields'][$id]['image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Avatar'),
        '#default_value' => $entity['image'],
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
        '#default_value' => $entity['url'],
      ];

      $form['table_options']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // TableDrag: Weight column element.
      $form['table_options']['table_fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $entity['label']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['fields-order-weight']],
      ];

      $form['table_options']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    kint($form_state->getValue(['table_options', 'table_fields']));
    exit();
    $this->configuration['table_fields'] = $form_state->getValue(['table_options', 'table_fields']);

    // If the user uploaded a new logo or favicon, save it to a permanent location
    // and use it in place of the default theme-provided file.
    $values = $form_state->getValue(['others', 'config']);
    if ($values['logo']['logo_path']) {
      $this->configuration['others']['logo']['path'] = $values['logo']['logo_upload'];
    }
    elseif ($values['logo']['logo_upload']) {
      $this->configuration['others']['logo']['image'] = $values['logo']['logo_upload'];
      $this->setFileAsPermanent($values['logo']['logo_upload']);
    }

    $this->configuration['others']['url'] = $values['url'];

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

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, filters_fields, table_fields.
    $this->cardBuildHeader($filters = FALSE, $columns = FALSE);

    // Se construye la variable $build con los datos que se necesitan en el tema
    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'general_logo',
      'library' => '',
    ];

    $src = '';

    if (!empty($this->configuration['others']['logo']['path'])) {
      $src = $this->configuration['others']['logo']['path'];
    }
    elseif (!empty($this->configuration['others']['logo']['image'][0])) {
      $file = file_load($this->configuration['others']['logo']['image'][0]);
      $src = file_create_url($file->getFileUri());
    }

    $logo = [
      'url' => $this->configuration['others']['url'],
      'src' => $src,
    ];

    // Parameter additional.
    $others = [
      '#logo' => $logo,
    ];

    $this->cardBuildVarBuild($parameters, $others);

    return $this->build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
