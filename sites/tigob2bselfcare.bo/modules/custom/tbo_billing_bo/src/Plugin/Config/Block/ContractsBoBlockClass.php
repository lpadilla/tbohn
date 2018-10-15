<?php

namespace Drupal\tbo_billing_bo\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_billing_bo\Plugin\Block\ContractsBoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

class ContractsBoBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_billing_bo\Plugin\Block\ContractsBoBlock $instance
   * @param $config
   */
  public function setConfig(ContractsBoBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [
          'contract' => ['title' => t("Contrato"),'label' => t('Contrato'), 'service_field' => 'contract', 'show' => 1, 'position' => 'right', 'weight' => 3, 'class' => '2-columns'],        
          'lines' => ['title' => t("Líneas"),'label' => t('Lineas'), 'service_field' => 'lines', 'show' => 1, 'position' => 'right', 'weight' => 3, 'class' => '2-columns'],
          'contract_expired' => ['title' => t("Deuda del contrato"),'label' => t('Deuda del contrato'), 'service_field' => 'contract_expired', 'show' => 1, 'position' => 'left', 'weight' => 4, 'class' => '2-columns'],
          'manage' => ['title' => t("ADMINISTRAR"),'label' => t('ADMINISTRAR'), 'service_field' => 'manage', 'show' => 0, 'weight' => 2, 'class' => 'double-top-and-bottom-padding'],
          'planes_lines' => ['title' => t("VER LINEAS Y PLANES"), 'label' => t('VER LINEAS Y PLANES'), 'service_field' => 'planes_lines', 'show' => 0, 'weight' => 1, 'class' => 'double-top-and-bottom-padding'], 
        ],
      ],
      'others' => [
        'config' => [
          'url_manage' => '',
          'url_payment' => '',
          'type_wrapper' => [
            'type' => 'movil',
          ],
          'money' => '$',
          'total_invoice' => 3,
          'noServices_wrapper' => [
            'description' => t('Descripción del card'),
            'label_button' => t('Texto del botón'),
            'url_button' => '',
          ],
          'show_exportar_contratos' => 0,
          'label_exportar' => t('EXPORTAR TODOS LOS CONTRATOS'),
          'url_balance' => t('lineas'),
          'segment' => 1,
          'titulo' => [
            'title_contract' => t('Contrato'),
          ],
        ],
      ],
      
      
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm(&$form, &$form_state, $configuration) {
    $this->configuration = $configuration;
    $others = $this->configuration['others']['config'];
    
    # Opciones de formulario
    

    $field['others']['config']['type_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => t('Tipo de servicio'),
    ];

    $field['others']['config']['type_wrapper']['type'] = [
      '#type' => 'radios',
      '#title' => t('Tipo de servicio'),
      '#title_display' => 'invisible',
      '#options' => [
        'movil' => t('Móvil'),
        //'fijo' => t('Fijo'),
      ],
      '#default_value' => $others['type_wrapper']['type'],
      '#required' => TRUE,
    ];

    $field['others']['config']['titulo'] = [
      '#type' => 'fieldset',
      '#title' => t('Titulo bloque contratos'),
    ];

    $field['others']['config']['titulo']['title_contract'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre título'),
      '#default_value' => $others['titulo']['title_contract'],
      '#description' => t('Ingrese el texto para el título'),
      '#required' => TRUE,
    ];

    $field['others']['config']['show_exportar_contratos'] = [
      '#type' => 'checkbox',
      '#title' => t('Exportar todos los contratos'),
      '#default_value' => $others['show_exportar_contratos'],
      
    ];

    $field['others']['config']['url_manage'] = [
      '#type' => 'url',
      '#title' => t('Url exportar'),
      '#default_value' => $others['url_manage'],
      '#required' => TRUE,
    ];

    $field['others']['config']['label_exportar'] = [
      '#type' => 'textfield',
      '#title' => t('Texto de exportar contratos'),
      '#default_value' => $others['label_exportar'],
      '#description' => t('Ingrese el texto de exportar contratos'),
      '#required' => TRUE,
    ];

    $field['others']['config']['url_balance'] = [
      '#type' => 'textfield',
      '#title' => t('Url de lineas'),
      '#default_value' => $others['url_balance'],
      '#description' => t('Ingrese la url de lineas'),
      '#required' => TRUE,
    ];

    $field['others']['config']['segment'] = [
      '#type' => 'checkbox',
      '#title' => t('Activar envio de segment'),
      //'#default_value' => $this->configuration['segment'],
     '#default_value' => $others['segment'],
    ];

    $field['others']['config']['total_invoice'] = [
      '#type' => 'textfield',
      '#title' => t('Número maximo de contratos a mostrar'),
      '#default_value' => $others['total_invoice'],
      '#description' => t('Digite la cantidad maxima de contratos a mostrar'),
      '#required' => TRUE,
    ];


    $form = $this->instance->cardBlockForm($field['others']['config']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ContractsBoBlock &$instance, $configuration) {
  	
    // Set data uuid, generate filters_fields, generate table_fields.
    $typeService = $configuration['others']['config']['type_wrapper']['type'];
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'contractsBoBlock');
    $instance->setValue('class', 'block-contracts-summary-message');
    $library = $title_no_services = '';

    if ($typeService == 'movil') {
      $library = 'tbo_billing_bo/contracts-bo';
      $instance->setValue('directive', 'data-ng-contracts-bo');
      $title_no_services = t('Servicios móviles');
    }
    
    // Set session var.
    $instance->cardBuildSession();

    $parameters = [
      'theme' => 'contracts_bo',
      'library' => $library,
    ];

    usort($configuration['table_options']['table_fields'], function ($a1, $a2) {
      $v1 = $a1['weight'];
      $v2 = $a2['weight'];
      return $v1 - $v2;
    });
    $display = '';


    $others = [
      '#display' => $display,
      '#fields' => $configuration['table_options']['table_fields'],
      '#type' => $typeService,
      '#url' => $configuration['others']['config']['url_manage'],
      '#export' => $configuration['others']['config']['show_exportar_contratos'],
      '#titleNoServices' => $title_no_services,
      '#segment' => $configuration['segment'],
      '#url_balance' => $configuration['others']['config']['url_balance'],
      '#title_exportar' => $configuration['others']['config']['label_exportar'],
      '#title' => $configuration['others']['config']['titulo']['title_contract'],
    ];

    if($_SESSION['company']['client_code']){
      $clients =$_SESSION['company']['client_code'];
    }
    $_SESSION['num_contracts']=0;
    $_SESSION['contracts_data']="";
    $_SESSION['ciclo_contratos']="";
    
    # Valores a pasar por getal servicicio, donde angular los recive con myconfigs
    $other_config = [
      'type' => $typeService,
      'cant_contratos' => $configuration['others']['config']['total_invoice'],
      'clients' => $clients,
    ];


    $config_block = $instance->cardBuildConfigBlock('/tboapi/billing/contracts_bo?_format=json', $other_config);
    $instance->cardBuildVarBuild($parameters, $others);
    $instance->cardBuildAddConfigDirective($config_block);
    if ($this->configuration['others']['config']['segment']) {    
     
        # Save segment track.
        $event = 'TBO - Contratos';
        $category = 'Contratos';    
        $label = 'Contrato para el Cliente - ' . $clients;
        \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);

   }
    return $instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit(&$form, &$form_state, &$configuration) {
    $configuration['table_options'] = $form_state->getValue(['table_options']);
    $configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $configuration['others_display'] = $form_state->getValue(['others_display']);
    $configuration['others'] = $form_state->getValue(['others']);
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