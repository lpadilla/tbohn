<?php

namespace Drupal\tbo_general_hn\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\Plugin\Block\LogoTigoUneFooterBlock;
use Drupal\tbo_general\CardBlockBase;
use Drupal\file\Entity\File;

/**
 * Provides a 'LogoTigoUneFooterHnBlock' block.
 *
 * @Block(
 *  id = "logo_tigo_une_footer_hn_block",
 *  admin_label = @Translation("Logo Footer B2B Hn"),
 * )
 */
class LogoTigoUneFooterHnBlock extends CardBlockBase  {


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' =>  [
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'logo' => [
            'title' => $this->t('Logo'),
            'service_field' => 'logo',
            'show' => 1,
            'weight' => 1,
          ],
        ],
      ],
      'others_display' => [],
      'buttons' => [],
      'others' => [
        'config' => [
          'logo' => [
            'path' => '',
            'image' => '',
          ],
          'url' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $field['logo']['logo_path'] = [
      '#type' => 'textfield',
      '#title' => t('Si conoce el path del logo agreguelo en este campo'),
      '#default_value' => $this->configuration['others']['config']['logo']['path'],
    ];

    $field['logo']['logo_upload'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Avatar'),
      '#default_value' => $this->configuration['others']['config']['logo']['image'],
      '#description' => $this->t('Por favor ingrese una imagen de formato PNG, JPEG, SVG y medidas minimas 177px X 24px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg svg'],
        'file_validate_image_resolution' => [$maximum_dimensions = 0, $minimum_dimensions = '177x24'],
      ],
    ];

    $field['url'] = [
      '#type' => 'textfield',
      '#title' => t('Url de redireccion del logo del sitio'),
      '#default_value' => $this->configuration['others']['config']['url'],
    ];

    $form = $this->cardBlockForm($field);

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
    $this->configuration['table_fields'] = $form_state->getValue(['table_options', 'table_fields']);

    // If the user uploaded a new logo or favicon, save it to a permanent location
    // and use it in place of the default theme-provided file.
    $values = $form_state->getValue(['others', 'config']);
    if ($values['logo']['logo_path']) {
      $this->configuration['others']['config']['logo']['path'] = $values['logo']['logo_upload'];
    }
    elseif ($values['logo']['logo_upload']) {
      $this->configuration['others']['config']['logo']['image'] = $values['logo']['logo_upload'];
      $this->setFileAsPermanent($values['logo']['logo_upload']);
    }

    $this->configuration['others']['config']['url'] = $values['url'];

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
    //Set data uuid, filters_fields, table_fields
    $this->cardBuildHeader($filters = FALSE, $columns = TRUE);

    //Se construye la variable $build con los datos que se necesitan en el tema
    $parameters = [
      'theme' => 'general_logo',
      'library' => '',
    ];

    $src = '';

    if (!empty($this->configuration['others']['config']['logo']['path'])) {
      $src = $this->configuration['others']['config']['logo']['path'];
    }
    elseif (!empty($this->configuration['others']['config']['logo']['image'][0])) {
      global $base_url;
      $file = \Drupal\file\Entity\File::load($this->configuration['others']['config']['logo']['image'][0]);
      $path= file_create_url('public://');
      $src = $path.$file->getFilename();
    }

    $logo = [
      'url' => $this->configuration['others']['config']['url'],
      'src' => $src,
    ];

    //Parameter additional
    $others = [
      '#logo' => $logo,
    ];

    $this->cardBuildVarBuild($parameters, $others);

    return $this->build;
  }

  
}
