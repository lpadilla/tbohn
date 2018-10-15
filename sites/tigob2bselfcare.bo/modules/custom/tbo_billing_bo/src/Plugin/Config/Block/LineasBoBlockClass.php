<?php

namespace Drupal\tbo_billing_bo\Plugin\Config\Block;

use Drupal\tbo_billing_bo\Plugin\Block\LineasBoBlock;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LineasBoBlockClass{
  protected $instance;
  protected $configuration;

   /**
   * @param LineasBoBlock $instance
   * @param $config
   */
  public function setConfig(LineasBoBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'filters_options' => [
        'filters_fields' => [
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'lines' => ['title' => t('Línea'), 'label' => t('Línea'), 'type' => 'user', 'service_field' => 'msisdn', 'show' => 1, 'weight' => 1,],
          'plan_consumo' => ['title' => t('Plan de consumo'), 'label' => t('Plan de consumo'), 'type' => 'user', 'service_field' => 'plan_consumo', 'show' => 1, 'weight' => 2,],
          'plan_datos' => ['title' => t('Plan de datos'), 'label' => t('Plan de datos'), 'type' => 'user', 'service_field' => 'plan_datos', 'show' => 1, 'weight' => 2,],
          'tele_group' => ['title' => t('TeleGroup'), 'label' => t('TeleGroup'), 'type' => 'user', 'service_field' => 'tele_group', 'show' => 1, 'weight' => 2,],
          'add_ons' => ['title' => t('Add-Ons'), 'label' => t('Add-Ons'), 'type' => 'user', 'service_field' => 'add_ons', 'show' => 1, 'weight' => 2,],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [ //opciones del paginador
            'number_pages' => 30,
            'number_rows_pages' => 10,
          ],
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'url_config' => 'exportar',
          'limit_lines' => 600,
          'show_export' => TRUE, //boton de exportar
          'url_balance' => 'saldos',
          'url_home' => 'inicio',
          'export_name' => 'EXPORTAR',
          'title_two' => 'Líneas y planes incluidos en tu contrato',
          'show_detail' => 'MOSTRAR DETALLES',
          'menor_fifty' => '...cargando...',
          'error_msg' => 'Error obteniendo los datos del servicio',
          'title_fijos' => 'FACTURA DE SERVICIOS FIJOS',
          'header' => [
            'detail' => [
              'header_line' => ['title' => t('Línea'), 'label' => t('Línea'), 'service_field' => 'header_line', 'show' => 1],
              'header_deuda' => ['title' => t('Deuda del contrato'), 'label' => t('Deuda del contrato'), 'service_field' => 'header_deuda', 'show' => 1],
            ],
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1
      ],
    );
  }

   /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    
    //configuracion para el encabezado del bloque
    $field['header'] = array(
      '#type' => 'details',
      '#title' => t('Configuraciones Encabezado'),
      '#open' => TRUE,
    );
    $field['header']['detail'] = array(
      '#type' => 'table',
      '#header' => array(t('Title'), t('Label'), t('Show'), t('')),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ),
    );

    $header = $this->configuration['others']['config']['header']['detail'];
    foreach ($header as $id => $entity) {      
      $field['header']['detail'][$id]['title'] = array(
        '#plain_text' => $entity['title'],
      );

      $field['header']['detail'][$id]['label'] = array(          
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      );

      $field['header']['detail'][$id]['show'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      );
    }

    //configuracion de iimite de lineas
    $field['limit_lines'] = [
      '#type' => 'textfield',
      '#title' => t('Número limite de lineas a obtener del servicio'),
      '#default_value' => $this->configuration['others']['config']['limit_lines'],
      '#description' => t('Digite la cantidad limite de lineas a obtener'),
      '#required' => TRUE,
    ];

    $field['title_fijos'] = [
      '#type' => 'textfield',
      '#title' => t('Titulo de servicios fijos'),
      '#default_value' => $this->configuration['others']['config']['title_fijos'],
      '#description' => t('Digite el titulo de servicios fijos'),
      '#required' => TRUE,
    ];

    $field['error_msg'] = [
      '#type' => 'textfield',
      '#title' => t('Mensaje de error al llamar al servicio'),
      '#default_value' => $this->configuration['others']['config']['error_msg'],
      '#description' => t('Digite el mensaje de error que se mostrara de fallar el servicio'),
      '#required' => TRUE,
    ];

    $field['url_balance'] = [
      '#type' => 'textfield',
      '#title' => t('Url de Saldos'),
      '#default_value' => $this->configuration['others']['config']['url_balance'],
      '#description' => t('Digite la url de saldos'),
      '#required' => TRUE,
    ];
    
    $field['url_home'] = [
      '#type' => 'textfield',
      '#title' => t('Url de inicio del site'),
      '#default_value' => $this->configuration['others']['config']['url_home'],
      '#description' => t('Digite la url de inicio del site'),
      '#required' => TRUE,
    ];

    $field['title_two'] = [
      '#type' => 'textfield',
      '#title' => t('Segundo titulo del bloque'),
      '#default_value' => $this->configuration['others']['config']['title_two'],
      '#description' => t('Digite el segundo titulo del bloque'),
      '#required' => TRUE,
    ];

    $field['menor_fifty'] = [
      '#type' => 'textfield',
      '#title' => t('Texto cargando'),
      '#default_value' => $this->configuration['others']['config']['menor_fifty'],
      '#description' => t('Digite el texto que se usará para mostrar en la tabla mientras se llenan las columnas desde el segundo servicio'),
      '#required' => TRUE,
    ];

    $field['show_detail'] = [
      '#type' => 'textfield',
      '#title' => t('Texto para link de detalles'),
      '#default_value' => $this->configuration['others']['config']['show_detail'],
      '#description' => t('Digite el texto para el link de detalles, sencible a minuscula y mayuscula'),
      '#required' => TRUE,
    ];

    $field['export_name'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre del boton exportar'),
      '#default_value' => $this->configuration['others']['config']['export_name'],
      '#description' => t('Digite el nombre para el boton exportar, sencible a mininusculas y mayusculas'),
      '#required' => TRUE,
    ];

    //configuracion para mostrar boton de exportar
    $field['show_export'] =[
      '#title' => t('¿Desea activar botón de exportar?'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['others']['config']['show_export'],
    ];

    $form = $this->instance->cardBlockForm($field);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {      
      $configuration['table_options'] = $form_state->getValue(['table_options']);
	    $configuration['others'] = $form_state->getValue(['others']);
  }

  /**
   * {@inheritdoc}
   */
  public function build(LineasBoBlock &$instance, $configuration) {
    

    $admin_line = \Drupal::request()->query->get('d'); //bandera para determinar si va directo a la consulta de saldo de la linea principal

    $flag = \Drupal::request()->query->get('flag'); //valor identificador para usar en arreglo en sesion($_SESSION['contracts_data'])

    ///$num_contract_client = \Drupal::request()->query->get('num_contract');
    //kint($num_contract_client);

    //bandera de contrato seleccionado    
    $_SESSION['flag'] = $flag;


    $contratos = $_SESSION['contracts_data_line'];    
    foreach ($contratos as $key => $val) {
      if($val['flag']==$flag){
        $contract   = $val['contract'];
        $lines    = $val['lineas'];
        $deuda    = $val['deuda'];
        break;
      }
    }

    $_SESSION['contract']['lines']  = $lines;
    $_SESSION['contract']['number'] = $contract;
    $_SESSION['contract']['deuda']  = $deuda;

    $num_contract_client=$_SESSION['num_contracts_line'];


    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'lineasBoBlock');
    $instance->setValue('directive', 'data-ng-lineas-bo');
    
    $library = $title_no_services = '';
    
    $instance->cardBuildSession();

    //Ordering table_fields
    $instance->ordering('table_fields', 'table_options');
    $data = array();

    foreach($instance->getValue('table_fields') as $key => $value){
      if($value['show'] == 1){
        $data[$key]['identifier'] = $key;
        $data[$key]['label'] = $value['label'];
        $data[$key]['service_field'] = $value['service_field'];
        $data[$key]['type'] = $value['type'];
        $class = array('field-'.$value['service_field'], $value['class']);
        $data[$key]['class'] = implode(" ", $class);
      }
    }
    
    $header = $this->configuration['others']['config']['header']['detail'];
   

    $header_line = array(
      "label"=>$header['header_line']['label'], 
      "show"=>$header['header_line']['show']
    );
    $header_deuda = array(
      "label"=>$header['header_deuda']['label'], 
      "show"=>$header['header_deuda']['show']
    );

    $library = 'tbo_billing_bo/lineas-bo';  
    $parameters = [
      'theme' => 'lineas_summary',
      'library' => $library,
    ];
    
    //si se hace clic sobre el boton de administrar en contratos
    if($admin_line == 'a'){
      $instance->getLines = \Drupal::service('tbo_billing_bo.get_lines');
      $resp = $instance->getLines->get();       

      $url_saldos = $this->configuration['others']['config']['url_balance'];

      $host  = $_SERVER['HTTP_HOST'];
      $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
      $extra = $url_saldos.'?l=0';
      header("Location: http://$host$uri/$extra");
      die();
    }

    $other_config = [      
      'limit_lines' => $configuration['others']['config']['limit_lines'],
      'error_msg' => $configuration['others']['config']['error_msg'],
      'num_contract_client' => $num_contract_client,
    ];
 
    $others = [
      '#display' => $display,
      '#fields' => $data,
      '#header_line'=> $header_line,
      '#header_deuda' => $header_deuda,
      '#export'=> $configuration['others']['config']['show_export'],      
      '#url' => $configuration['others']['config']['url_details'],
      '#titleNoServices' => $title_no_services,
      '#url_config' => $configuration['others']['config']['url_config'],
      '#extra' => $this->configuration['others']['config']['url_balance'],
      '#url_home' => $this->configuration['others']['config']['url_home'],
      '#export_name' => $this->configuration['others']['config']['export_name'],
      '#title_two' => $this->configuration['others']['config']['title_two'],
      '#show_detail' => $this->configuration['others']['config']['show_detail'],
      '#menor_fifty' => $this->configuration['others']['config']['menor_fifty'],
      '#title_fijos' => $this->configuration['others']['config']['title_fijos'],
    ];

    $config_block = $instance->cardBuildConfigBlock('/tboapi/billing/lineas_bo?_format=json', $other_config);
    $config_block1 = $instance->cardBuildConfigBlock('/tboapi/billing/linea_detail_bo?_format=json', $other_config);
    $instance->cardBuildVarBuild($parameters, $others);
    $instance->cardBuildAddConfigDirective($config_block,'lineasBoBlock');
    $instance->cardBuildAddConfigDirective($config_block1,'lineasdetailBoBlock');


    // Guardando log auditoria
    // Load fields account.
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }

    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data_log[].
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Lineas',
      'description' => 'Usuario consulta Lineas del contrato '.$contract,
      'details' => 'Usuario ' . $name . ' ' . 'consultó las lineas del contrato '.$contract,
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);
    return $instance->getValue('build');
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