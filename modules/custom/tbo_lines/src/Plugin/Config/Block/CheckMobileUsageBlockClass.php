<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\tbo_lines\Plugin\Block\CheckMobileUsageBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Url;

/**
 * Manage config a 'CheckMobileUsageBlock' block.
 */
class CheckMobileUsageBlockClass {
  protected $instance;
  protected $configuration;
	protected $tbo_config;
	protected $api;

  /**
   * Constructs a new CheckMobileDetailsUsageService object.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /**
   * @param CheckMobileUsageBlock $instance
   * @param $config
   */
  public function setConfig(CheckMobileUsageBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  public function blockForm() {
    $form = [];
    $form['others'] = [];

    $form['others']['image_icon'] = [
      '#title' => t('Icono imagen', [], ['langcode' => 'es']),
      '#type' => 'managed_file',
      '#default_value' => $this->configuration['others']['config']['image_icon'],
      '#description' => t('Ventana de ayuda CVV, por favor ingrese una imagen de formato PNG, JPEG y medidas minimas aa px X bb px'),
      '#upload_location' => 'public://',
      '#upload_validators' => array(
        'file_validate_extensions' => array('png jpg'),
      ),
    ];

    return $this->instance->cardBlockForm($form['others'],[]);
  }

  
  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface &$form_state, &$config) {
    $fid = reset($form_state->getValue('others')['config']['image_icon']);
    // Save file permanently.
    if ($fid) {
      $this->setFileAsPermanent($fid);
    }
  }


    /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }
    $roles = $account->getRoles();
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

  public function build(CheckMobileUsageBlock &$instance, &$config) {

    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'sepUpInvoiceDeliveryBlock');
    $this->instance->setValue('directive', 'data-ng-check-mobile-usage');
    $this->instance->setValue('class', 'set-up-mobile-usage set-up-invoice-delivery');

    $msisdn = $_SESSION['serviceDetail']['address'];
    //  Agrega la variable de session para permitir transferencias con este numero
    $_SESSION['allowedMobileAccess'][$msisdn] = true;

    global $base_url;

    $fields = $this->instance->getValue('table_fields');

    $file = \Drupal\file\Entity\File::load(reset($this->configuration['others']['config']['image_icon']));
    if ($file) {
      $src = file_create_url($file->getFileUri());
    }

    $numIntentos = '3';
    if (isset($config['table_options']['table_fields']['numero_de_intentos']['label'])) {
      $numIntentos = $config['table_options']['table_fields']['numero_de_intentos']['label'];
    }
    $_SESSION['ver_consumo']['numero_de_intentos'] = $numIntentos;
    $valorMinimoACompartir = '501';
    if (isset($config['table_options']['table_fields']['valor_minimo_a_compartir']['label'])) {
      $valorMinimoACompartir = $config['table_options']['table_fields']['valor_minimo_a_compartir']['label'];
    }
    $_SESSION['ver_consumo']['valor_minimo_a_compartir'] = $valorMinimoACompartir;
    $valorMaximoACompartir = '20000';
    if (isset($config['table_options']['table_fields']['valor_maximo_a_compartir']['label'])) {
      $valorMaximoACompartir = $config['table_options']['table_fields']['valor_maximo_a_compartir']['label'];
    }
    $_SESSION['ver_consumo']['valor_maximo_a_compartir'] = $valorMaximoACompartir;


    $build = array(
      '#theme' => 'check_mobile_usage',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#fields' => $fields,
      '#class' => $this->instance->getValue('class'),
      '#id' => 'block-card',
      '#filters_mobile' => $filter_mobile,
      '#form' => $form,
      '#data' => [
        'image_icon' => $src,
        'valor_minimo_a_compartir_sin_formato' => $valorMinimoACompartir,
        'valor_maximo_a_compartir_sin_formato' => $valorMaximoACompartir,
        'valor_minimo_a_compartir' => $this->tbo_config->formatCurrency($config['table_options']['table_fields']['valor_minimo_a_compartir']['label']),
        'valor_maximo_a_compartir' => $this->tbo_config->formatCurrency($config['table_options']['table_fields']['valor_maximo_a_compartir']['label']),
        'costo_operacion' => $this->tbo_config->formatCurrency($config['table_options']['table_fields']['costo_operacion']['label']),
        'numero_de_intentos' => $numIntentos,
        'boton_primario_movil' => ucfirst(strtolower($config['buttons']['table_fields']['boton_primario']['label'])),
      ],
      '#showoptions' => $validate,
      '#display' => $type_display,
      '#checked' => $config_type,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#more_options' => $this->configuration['others']['config']['more_options'],
      '#buttons' => $this->configuration['buttons']['table_fields'],
      '#attached' => array(
        'library' => array(
          'tbo_lines/check-mobile-usage'
        ),
      ),
    );

    $_SESSION['serviceDetail']['transferMaxValue']=$config['table_options']['table_fields']['valor_minimo_a_compartir']['label'];
    $_SESSION['serviceDetail']['transferMinValue']=$config['table_options']['table_fields']['valor_maximo_a_compartir']['label'];
    $this->instance->setValue('build', $build);

    $billing_url = '/tbolines/check-mobile-usage/' . $msisdn . '?_format=json';//{phone_number}
    $other_config = [
      'transfer_balance' => '/tbolines/transfer-balance/{phone_number_origin}/{phone_number_destiny}/{value}?_format=json',
      'cancel_transfer_balance' => '/tbolines/check-mobile-usage-cancel-transfer/{phone_number_origin}/{phone_number_destiny}/{value}?_format=json',
      'msisdh' => $msisdn,
      'mensajes_de_error' => [
        'saldo_minimo_insuficiente' => t('El monto a compartir está por fuera del rango permitido. Por favor modifíquelo e intente de nuevo.'),
        'saldo_maximo_insuficiente' => t('El monto a compartir está por fuera del rango permitido. Por favor modifíquelo e intente de nuevo.'),
        'saldo_insuficiente' => t('Su saldo no es suficiente. Por favor modifique el monto a compartir e intente de nuevo'),        
        'numero_celular_invalido' => t('Número de celular invalido'),
      ],
      'numero_de_intentos' => $numIntentos,
    ];
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($billing_url, $other_config);
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'checkMobileUsage');

    //build
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
          'saldo_en_dinero' => ['title' => t('Saldo en dinero'), 'label' => 'Saldo en dinero', 'service_field' => 'saldo_en_dinero', 'show' => 1, 'weight' => 1],
          'compartir_saldo' => ['title' => t('Compartir saldo'), 'label' => 'Compartir saldo', 'service_field' => 'compartir_saldo', 'show' => 1, 'weight' => 1],
          'monto_a_compartir' => ['title' => t('Monto a compartir'), 'label' => 'Monto a compartir', 'service_field' => 'monto_a_compartir', 'show' => 1, 'weight' => 1],
          'numero_del_destinatario' => ['title' => t('Número del destinatario'), 'label' => 'Número del destinatario', 'service_field' => 'numero_del_destinatario', 'show' => 1, 'weight' => 1],

          'confirmar_la_transferencia' => ['title' => t('Confirmar la transferencia'), 'label' => 'Confirmar la transferencia', 'service_field' => 'confirmar_la_transferencia', 'show' => 1, 'weight' => 1],
          'numero_del_destino' => ['title' => t('Número del destino'), 'label' => 'Número del destino:', 'service_field' => 'numero_del_destino', 'show' => 1, 'weight' => 1],
          'valor_a_transferir' => ['title' => t('Valor a transferir'), 'label' => 'Valor a transferir', 'service_field' => 'valor_a_transferir', 'show' => 1, 'weight' => 1],
          'costo_de_la_operacion' => ['title' => t('Costo de la operación'), 'label' => 'Costo de la operación:', 'service_field' => 'costo_de_la_operacion', 'show' => 1, 'weight' => 1],
          'total_de_la_transacción' => ['title' => t('Total de la transacción'), 'label' => 'Total de la transacción:', 'service_field' => 'total_de_la_transacción', 'show' => 1, 'weight' => 1],

          'valor_minimo_a_compartir' => ['title' => t('Valor minimo a compartir'), 'label' => '501', 'service_field' => 'valor_minimo_para_compartir', 'show' => 1, 'weight' => 2], 
          'valor_maximo_a_compartir' => ['title' => t('Valor maximo a compartir'), 'label' => '20000', 'service_field' => 'valor_maximo_para_compartir', 'show' => 1, 'weight' => 3], 
          'costo_operacion' => ['title' => t('Costo operación'), 'label' => '249', 'service_field' => 'costo_operacion', 'show' => 1, 'weight' => 4], 

          'numero_de_intentos' => ['title' => t('Número de intentos'), 'label' => '3', 'service_field' => 'numero_de_intentos', 'show' => 1, 'weight' => 5], 
        ],
      ],
      'others' => [
        'config' => [
          'image_icon' => '',
          'min_val' => 0,
          'show_margin' => [
            'show_margin_card' => 1,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'boton_primario' => [
            'title' => t('Botón de compartir saldo'),
            'label' => 'COMPARTIR SALDO',
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
          'boton_secundario' => [
            'title' => t('Botón recargar'),
            'label' => 'RECARGAR',
            'url' => t('/detalle-servicios'),
            'url_description' => t('Ejemplo /detalle-servicios'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
          'servicios_de_valor_agregado' => [
            'title' => t('Servicios de valor agregado'),
            'label' => 'Servicios de valor agregado',
            'url' => t('/detalle-servicios'),
            'url_description' => t('Ejemplo /detalle-servicios'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
          'tigo_te_presta' => [
            'title' => t('Tigo te presta'),
            'label' => 'Tigo te presta',
            'url' => t('/detalle-servicios'),
            'url_description' => t('Ejemplo /detalle-servicios'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
          'mensajes_multimedia' => [
            'title' => t('Mensajes multimedia'),
            'label' => 'Mensajes multimedia',
            'url' => t('/detalle-servicios'),
            'url_description' => t('Ejemplo /detalle-servicios'),
            'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
          ],
        ],
      ],
      'not_show_class' => [],
    );
  }
}