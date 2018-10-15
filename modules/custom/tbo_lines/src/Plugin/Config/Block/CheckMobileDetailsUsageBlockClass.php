<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\tbo_lines\Plugin\Block\CheckMobileDetailsUsageBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Manage config a 'CheckMobileDetailsUsageBlock' block.
 */
class CheckMobileDetailsUsageBlockClass {
  protected $instance;
  protected $configuration;
  /**
   * @param CheckMobileDetailsUsageBlock $instance
   * @param $config
   */
  public function setConfig(CheckMobileDetailsUsageBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }



  public function blockForm() {
    $form = [];
    $form['others'] = [];

    $form['others']['icono_voz'] = [
      '#title' => t('Icono voz', [], ['langcode' => 'es']),
      '#type' => 'managed_file',
      '#default_value' => $this->configuration['others']['config']['icono_voz'],
      '#description' => t('Ventana de ayuda CVV, por favor ingrese una imagen de formato PNG, JPEG y medidas minimas aa px X bb px'),
      '#upload_location' => 'public://',
      '#upload_validators' => array(
        'file_validate_extensions' => array('png jpg'),
      ),
    ];

    $form['others']['icono_datos'] = [
      '#title' => t('Icono datos', [], ['langcode' => 'es']),
      '#type' => 'managed_file',
      '#default_value' => $this->configuration['others']['config']['icono_datos'],
      '#description' => t('Ventana de ayuda CVV, por favor ingrese una imagen de formato PNG, JPEG y medidas minimas aa px X bb px'),
      '#upload_location' => 'public://',
      '#upload_validators' => array(
        'file_validate_extensions' => array('png jpg'),
      ),
    ];

    $form['others']['icono_sms'] = [
      '#title' => t('Icono SMS', [], ['langcode' => 'es']),
      '#type' => 'managed_file',
      '#default_value' => $this->configuration['others']['config']['icono_sms'],
      '#description' => t('Ventana de ayuda CVV, por favor ingrese una imagen de formato PNG, JPEG y medidas minimas aa px X bb px'),
      '#upload_location' => 'public://',
      '#upload_validators' => array(
        'file_validate_extensions' => array('png jpg'),
      ),
    ];

    $valMostrarDetalleVoz = isset($this->configuration['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_voz'])?$this->configuration['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_voz']:'1';
    $valMostrarDetalleDatos = isset($this->configuration['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_datos'])?$this->configuration['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_datos']:'1';
    $valMostrarDetalleSMS = isset($this->configuration['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_sms'])?$this->configuration['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_sms']:'1';
    $form['others']['shos_details_data'] = [
      'ourfieldset' => [
        '#type' => 'fieldset',
        '#title' => t('Detalles a motrar'), 
        '#prefix' => '<div class="poll-form">',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        'mostrar_detalle_voz' => [
          '#type' => 'checkbox', 
          '#title' => t('Mostrar detalle de voz'), 
          '#return_value' => 1, 
          '#default_value' => $valMostrarDetalleVoz, 
          '#description' => t("Muestra todo el detalle de voz"),
        ],
        'mostrar_detalle_datos' => [
          '#type' => 'checkbox', 
          '#title' => t('Mostrar detalle de datos'), 
          '#return_value' => 1, 
          '#default_value' => $valMostrarDetalleDatos,
          '#description' => t("Muestra todo el detalle de voz"),
        ],
        'mostrar_detalle_sms' => [
          '#type' => 'checkbox', 
          '#title' => t('Mostrar detalle de voz'), 
          '#return_value' => 1, 
          '#default_value' => $valMostrarDetalleSMS,
          '#description' => t("Muestra todo el detalle de voz"),
        ],
        '#suffix' => '</div>', 
        '#collapsible' => FALSE,
        '#collapsed' => FALSE,
        '#tree' => TRUE,
      ]
    ];

    return $this->instance->cardBlockForm($form['others'],[]);
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



  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface &$form_state, &$config) {
    $fid = reset($form_state->getValue('others')['config']['icono_voz']);
    // Save file permanently.
    if ($fid) {
      $this->setFileAsPermanent($fid);
    }
    $fid = reset($form_state->getValue('others')['config']['icono_datos']);
    // Save file permanently.
    if ($fid) {
      $this->setFileAsPermanent($fid);
    }
    $fid = reset($form_state->getValue('others')['config']['icono_sms']);
    // Save file permanently.
    if ($fid) {
      $this->setFileAsPermanent($fid);
    }
  }

  public function build(CheckMobileDetailsUsageBlock &$instance, &$config) {
    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_na  me', 'sepUpInvoiceDeliveryBlock');
    $this->instance->setValue('directive', 'data-ng-check-mobile-details-usage');
    $this->instance->setValue('class', 'set-up-invoice-delivery');

    $msisdn = $_SESSION['serviceDetail']['address'];

    //  Agrega la variable de session para permitir transferencias con este numero
    $_SESSION['allowedMobileAccess'][$msisdn] = true;

    global $base_url;

    $fields = $this->instance->getValue('table_fields');

    $file = \Drupal\file\Entity\File::load(reset($this->configuration['others']['config']['icono_voz']));
    if ($file) {
      $srcIconoVoz = file_create_url($file->getFileUri());
    }
    $file = \Drupal\file\Entity\File::load(reset($this->configuration['others']['config']['icono_datos']));
    if ($file) {
      $srcIconoDatos = file_create_url($file->getFileUri());
    }
    $file = \Drupal\file\Entity\File::load(reset($this->configuration['others']['config']['icono_sms']));
    if ($file) {
      $srcIconoSMS = file_create_url($file->getFileUri());
    }

    $numOfCols = 0;
    if($config['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_voz']) {
      $numOfCols++;
    }
    if($config['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_datos']) {
      $numOfCols++;
    }
    if($config['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_sms']) {
      $numOfCols++;
    }

    $build = array(
      '#theme' => 'check_mobile_details_usage',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#fields' => $fields,
      '#class' => $this->instance->getValue('class'),
      '#id' => 'block-card',
      '#filters_mobile' => $filter_mobile,
      '#data' => [
        'icono_voz' => $srcIconoVoz,
        'icono_datos' => $srcIconoDatos,
        'icono_sms' => $srcIconoSMS,
        'mostrar_detalle_voz' => $config['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_voz'],
        'mostrar_detalle_datos' => $config['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_datos'],
        'mostrar_detalle_sms' => $config['others']['config']['shos_details_data']['ourfieldset']['mostrar_detalle_sms'],
        'ancho_en_desktop' => ($numOfCols>0?12/$numOfCols:4),
      ],
      '#form' => $form,
      '#showoptions' => $validate,
      '#display' => $type_display,
      '#checked' => $config_type,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#more_options' => $this->configuration['others']['config']['more_options'],
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#attached' => [
        'library' => array(
          'tbo_lines/check-mobile-details-usage'
        ),
      ]
    );
    //build
    $this->instance->setValue('build', $build);

    $billing_url='/tbolines/check-mobile-details-usage/' . $msisdn . '?_format=json';
    $other_config = [
      'msisdh' => $msisdn,
      'msisdh_det' => $msisdn,
      'url_to_log_data_buttons' => '/tbolines/ccheck-mobile-details-logs-button-usage/{phone_number_origin}/{categoria}?_format=json',
    ];
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($billing_url, $other_config);
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'checkMobileDetailsUsage');

    $build['#cache']['max-age'] = 0;
    return $this->instance->getValue('build');
  }

  /**
   * Method to save file permanenty in the database
   * @param string $fid
   *    File id
   */

  public function setFileAsPermanent($fid) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    $file = File::load($fid);

    //If file doesn't exist return
    if (!is_object($file)) {
      return;
    }

    //Set as permanent
    $file->setPermanent();

    // Save file
    $file->save();

    // Add usage file
    \Drupal::service('file.usage')->add($file, 'tbo_lines', 'tbo_lines', 1);
  }
  
  public function defaultConfiguration() {
    return array(
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [
          'texto_voz' => ['title' => t('Texto voz'), 'label' => 'Voz', 'service_field' => 'texto_voz', 'show' => 1, 'weight' => 3], 
          'unidad_voz' => ['title' => t('Unidad voz'), 'label' => 'MIN', 'service_field' => 'unidad_voz', 'show' => 1, 'weight' => 3], 
          'texto_datos' => ['title' => t('Texto datos'), 'label' => 'Datos', 'service_field' => 'texto_datos', 'show' => 1, 'weight' => 3], 
          'unidad_datos' => ['title' => t('Unidad datos'), 'label' => 'GB', 'service_field' => 'unidad_datos', 'show' => 1, 'weight' => 3], 
          'texto_sms' => ['title' => t('Texto SMS'), 'label' => 'SMS', 'service_field' => 'texto_sms', 'show' => 1, 'weight' => 3], 
          'unidad_sms' => ['title' => t('Unidad SMS'), 'label' => 'SMS', 'service_field' => 'unidad_sms', 'show' => 1, 'weight' => 3], 
        ],
      ],
      'others' => [
        'config' => [
          'data',
          'icono_voz',
          'icono_datos',
          'icono_sms',
          'show_margin' => [
            'show_margin_card' => 1,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'detalle_voz' => [
            'title' => t('Botón detalle de voz'),
            'label' => t('DETALLES VOZ'),
            'url' => t('/detalles-voz'),
            'url_description' => t('Ejemplo /detalles-voz'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
          'detalle_datos' => [
            'title' => t('Botón detalle de datos'),
            'label' => t('DETALLES DATOS'),
            'url' => t('/detalles-datos'),
            'url_description' => t('Ejemplo /detalles-datos'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
          'detalle_sms' => [
            'title' => t('Botón detalle de sms'),
            'label' => t('DETALLES SMS'),
            'url' => t('/detalles-sms'),
            'url_description' => t('Ejemplo /detalled-sms'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1
      ],
    );
  }
}