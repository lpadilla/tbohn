<?php

namespace Drupal\tbo_general\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_general\Form\CompanySelector;
use Drupal\file\Entity\File;

/**
 * Class CompanySelectorForm config.
 */
class CompanySelectorFormClass {

  protected $instance;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_selector';
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance(CompanySelector &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config) {
    $form["#tree"] = TRUE;

    $form['container'] = [
      '#type' => 'details',
      '#title' => t('Configuración selector de empresas'),
      '#open' => TRUE,
    ];

    $form['container']['visibility'] = [
      '#type' => 'fieldset',
      '#title' => t('Información a mostrar'),
    ];

    $form['container']['visibility']['show_avatar'] = [
      '#type' => 'checkbox',
      '#title' => t('Avatar de la empresa'),
      '#default_value' => $config->get('container')['visibility']['show_avatar'],
    ];

    $form['container']['visibility']['show_name'] = [
      '#type' => 'checkbox',
      '#title' => t('Nombre de la empresa'),
      '#default_value' => $config->get('container')['visibility']['show_name'],
    ];

    $form['container']['visibility']['show_mail'] = [
      '#type' => 'checkbox',
      '#title' => t('Mail de la empresa'),
      '#default_value' => $config->get('container')['visibility']['show_mail'],
    ];

    $form['container']['visibility']['show_button'] = [
      '#type' => 'checkbox',
      '#title' => t('Botón'),
      '#default_value' => $config->get('container')['visibility']['show_button'],
    ];

    $form['container']['avatar'] = [
      '#type' => 'managed_file',
      '#title' => t('Avatar'),
      '#default_value' => $config->get('container')['avatar'],
      '#description' => t('Por favor ingrese una imagen de formato PNG, JPEG, SVG y medidas minimas 54 px X 56 px'),
      '#upload_location' => 'public://avatar',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg svg'],
        'file_validate_image_resolution' => [
          $maximum_dimensions = 0,
          $minimum_dimensions = '54x56',
        ],
      ],
    ];

    $form['container']['redirect_button'] = [
      '#type' => 'fieldset',
      '#title' => t('Configuraciones del botón'),
    ];

    $form['container']['redirect_button']['url'] = [
      '#type' => 'url',
      '#title' => t('URL'),
      '#description' => t('url de redirección'),
      '#default_value' => $config->get('container')['redirect_button']['url'],
    ];

    $form['container']['redirect_button']['label'] = [
      '#type' => 'textfield',
      '#title' => t('Etiqueta'),
      '#description' => t('label para link de redirección'),
      '#default_value' => $config->get('container')['redirect_button']['label'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('container')['avatar'];
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
