<?php

namespace Drupal\tbo_balance_bo\Plugin\Config\Block;

use Drupal\tbo_balance_bo\Plugin\Block\BalancesBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'BalancesBoBlockClass' block.
 */
class BalancesBoBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param BalancesBlock $instance
   * @param $config
   */
  public function setConfig(BalancesBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(  
      'filters_options' => [
        'filters_fields' =>[]
      ],    
      'table_options' => [
        'table_fields' => []
      ],
      'others' => [
        'config' => [
          'amount' => '20,50,100,150,200,250',
          'commission' => 0,  
          'linea_principal' => 0,
          'plan' => 0,  
          'segment' => 1,
          'plan_2' => 0,
          'addons' => 0,
          'telegroup'=> 0, 
        ]
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    //Vista de transferencias    
    $form['others']['config']['amount'] = [
      '#type' => 'textfield',
      '#title' => t('Montos para Transferencia'),
      '#default_value' => $this->configuration['others']['config']['amount'],
    ];
    $form['others']['config']['commission']= [
      '#type' => 'checkbox',
      '#title' => t('Activar comisión de Transferencia'),
      '#default_value' => $this->configuration['others']['config']['commission'],
    ];
    $form['others']['config']['linea_principal']= [
      '#type' => 'checkbox',
      '#title' => t('Mostrar columna de Linea principal en el encabezado'),
      '#default_value' => $this->configuration['others']['config']['linea_principal'],
    ];
    $form['others']['config']['plan']= [
      '#type' => 'checkbox',
      '#title' => t('Mostrar columna de Plan en el encabezado'),
      '#default_value' => $this->configuration['others']['config']['plan'],
    ];   
    $field['others']['config']['segment'] = [
      '#type' => 'checkbox',
      '#title' => t('Activar envio de segment'),
      //'#default_value' => $this->configuration['segment'],
     '#default_value' => $this->configuration['others']['config']['segment'],
    ]; 

    $form['others']['config']['plan_2']= [
      '#type' => 'checkbox',
      '#title' => t('Mostrar columna de Plan de Datos en el encabezado'),
      '#default_value' => $this->configuration['others']['config']['plan_2'],
    ]; 
    $form['others']['config']['addons']= [
      '#type' => 'checkbox',
      '#title' => t('Mostrar columna de Addons en el encabezado'),
      '#default_value' => $this->configuration['others']['config']['addons'],
    ];    
    $form['others']['config']['telegroup']= [
      '#type' => 'checkbox',
      '#title' => t('Mostrar columna de Telegroup en el encabezado'),
      '#default_value' => $this->configuration['others']['config']['telegroup'],
    ];

    $form = $this->instance->cardBlockForm($form['others']['config']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $all = $form_state->getValue('others');
    $this->configuration['others']['config'] = $all['config'];
    $this->blockForm();
  }

  /**
   * {@inheritdoc}
   */
  public function build(BalancesBlock &$instance, &$config){
    $this->api = \Drupal::service('tbo_api_bo.client'); 

    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('config_name', 'BalancesBlock');
    $this->instance->setValue('directive', 'data-ng-invoice-delivery');
    $this->instance->setValue('class', 'balances');

    $amounts = $this->configuration['others']['config']['amount'];

    $plan_show = $this->configuration['others']['config']['plan'];
    $linea_principal_show = $this->configuration['others']['config']['linea_principal'];
    $plan_2_show = $this->configuration['others']['config']['plan_2'];
    $addons_show = $this->configuration['others']['config']['addons'];
    $telegroup_show = $this->configuration['others']['config']['telegroup'];

    $_SESSION['amounts_tf'] = $amounts;

    $modal = [
      'data' => $this->configuration['others_display'],
      'environment' => $_SESSION['environment'],
    ];

    //set title
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = t("Transferencia de saldo");
    }

    //BalancesBoForm
    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_balance_bo\Form\BalancesBoForm',$this->configuration);

    $data = "1";
    
    $l = $_GET['l'];
    $array_lineas = $_SESSION['lineas_data'];

    $msisdn_rest=$msisdn=$plan=$linea_principal=null;
    $lineas = $lineas_rel = array();
    // se obtiene la linea que se selecciono en la pantalla de lineas o desde administrar
    foreach ($array_lineas['datos'] as $key) {
      if($key['l'] == $l){
        $msisdn = $key['msisdn'];
        $msisdn_rest = $key['msisdn'];
        $plan = $key['plans']['PlanType']['planName'];
      }


      if($key['l']==0){
        $linea_principal = $key['msisdn'];
      }
      $lineas_rel[$key['msisdn']] = array($key['l']);
      array_push($lineas, $key['msisdn']);
    }
    
    $lineas_rel_json = json_encode($lineas_rel);
   
    $prefix_country = \Drupal::config('adf_rest_api.settings')->get('prefix_country');
    $msisdn = $this->_validatePhone($msisdn, $prefix_country);
   

    $config = \Drupal::config("tbo_billing_bo.bill_payment_settings");
    $group = "visualizacion";

    if($msisdn != null && $msisdn != "" && !empty($msisdn) && isset($msisdn)){
      /* Section Consulta de Saldo */
        $params['query'] = [
        ];
        
        $params['tokens'] = [
          'msisdn' => $msisdn,     
        ];
        
             
        try {            
          //llamado al servicio de saldos
          $response = $this->api->getBalanceInquiry($params); 
          

          if($response != FALSE){            
            $balance['Bs'] = $balance['mb'] = $balance['seg'] = $balance['sms'] = null;
            $megas  = array();

            
            foreach ($response->balances as $key) {
              $unit= strtolower($key->unit);
              if($key->description == 'Saldo'){
                $unit = 'Bs';
              }        
              if(isset($key->expirationDate)){
                $dates_ex = $key->expirationDate;
              }else if(isset($key->rolloverExpirationDate)){         
                $dates_ex = $key->rolloverExpirationDate;
              }else{
                $dates_ex = t('No Disponible');
              }
              $date1 = date_create($dates_ex);
              $date2 = date_create(date("Y-m-d H:i:s"));
              $date_diff = date_diff($date1, $date2); 
              if($dates_ex != 'No Disponible' ) { 
                  $period_format = format_date(strtotime($dates_ex), 'fullmeridian');
                  $show_date="Vence: ". $period_format; 
              }else{
                  $show_date = "Vence: ".$dates_ex;
              }  
              if($unit=='mb'){             
                $balance[$unit]['amount']= $key->balanceAmount;
                $monto=$balance[$unit]['amount'];          
                

                $arr = array();
                $arr = [
                  'description' => $key->description,
                  'amount' => $monto,
                  'date' => $show_date,
                ];
               array_push($megas,  $arr);
              }else{
                  $balance[$unit]['amount'] = $key->balanceAmount;
                  $balance[$unit]['unit'] = $key->unit; 
                  $balance[$unit]['description'] = $key->description;    
                  $balance[$unit]['expirationDate'] = $show_date;           
              }
              if($key->description == 'Saldo'){
                $balance[$unit]['unit'] = 'Bs';          
              }        
            }
            if($balance['Bs'] == null){
              $balance['Bs']['unit'] = 'Bs';
              $balance['Bs']['amount'] = '0';
            }
          }else{
            drupal_set_message(t('Error en la respuesta del servicio de Saldo'), 'error');
          }
        }catch (\Exception $e) {
          drupal_set_message(t('Error en la reespuesta del servicio de Saldo'), 'error');
        }
    
        $msisdn = substr($msisdn, 3); 
      	$linea_flag =  $_SESSION['flag'];  
      /* End section consulta de saldo*/

      /* Section detalles de linea telegroup, addons y plan de datos */
        $params1['query'] = [
         //'msisdn' => $msisdn,
        ];      
        $params1['tokens'] = [
          //'msisdn' => $msisdn_rest, 
          'msisdn' => $msisdn,
        ];
  
        try {
          //llamado al servicio de detalle de linea
          $response1 = $this->api->getCustomerBasicPlanInfoByMsisdn($params1);

         // $statusCode = 'AC';
         // $statusCode = $this->determinateStatus($statusCode);
          //  $result['status'] = $statusCode[0];
          if($response1 != FALSE){
           // $result['status'] = $response1->statusPhone->status;
            $statusCode = $response1->statusPhone->statusCode;     
            $statusCode = $this->determinateStatus($statusCode);       
          //  $result['status'] = $this->determinateStatus($statusCode);
            $result['plan'] = $response1->planBasicInfo->subPlan;
            $result['addons'] = $response1->addons->addons;
            $result['telegroup'] = $response1->telegroup->telegroup;

          }else{            
            drupal_set_message(t('Error en el detalle del Servicio'), 'error');
          }
        } catch (\Exception $e) {
          drupal_set_message(t('Error en el detalle del Servicio'), 'error');
          //return new ResourceResponse(UtilMessage::getMessage($e));
        }
      /* End section detalles de linea*/
     
      
      $build = array(
        '#theme' => 'balances_bo',
        '#directive' => $this->instance->getValue('directive'),
        '#class' => $this->instance->getValue('class'),
        '#form'=> $form,
        '#formName' => 'balances-form',
        '#balance' => $balance,
        '#msisdn' => $msisdn,
        '#megas' => $megas,
        '#fecha'=> $fecha,
        '#lineas' => $lineas,
        '#lineas_rel' => $lineas_rel_json,
        '#modal' => $modal,
        '#plan' => $plan,
        '#plan_show' => $plan_show,
        '#flag' => $linea_flag,
        '#linea_principal'=> $linea_principal,
        '#linea_principal_show'=> $linea_principal_show,      
        '#status' => $statusCode[0],
        '#status2' => $statusCode[1],
        '#plan_2' => $result['plan'],
        '#addons' => $result['addons'],
        '#telegroup' => $result['telegroup'],
        '#plan_2_show' => $plan_2_show,
        '#addons_show' => $addons_show,
        '#telegroup_show' => $telegroup_show,
        '#attached' => array(
          'library' => array(
            'tbo_balance_bo/balances',
          ),
        ),
      );
      # Save segment track.
      $event = 'TBO - Consulta de Saldos';
      $category = 'Saldos';    
      $label = 'Consulta de Saldo para la linea- ' . $linea_flag;
      \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
    }else
    {
      drupal_set_message(t('El msisdn se encuentra vacio'), 'error');
      $build = [];
    }

    //build
    $this->instance->setValue('build', $build);

    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account){
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
   * @param $phone_number 
   * @param $prefix_country
   * @return string   #591
   */
  public function _validatePhone($phone_number,$prefix_country){
    
    $expresion = '/^['.$prefix_country.'][0-9]{10}$/';
     if(preg_match($expresion, $phone_number)){
      return $phone_number;
     }else{        
        $phone_number = $prefix_country . $phone_number;
     } 
     return $phone_number;
  }
  /**
   * @param $status
   * @return string  
   */
  public function determinateStatus($status){
  

	  if ($status == 'AC') {
	    $status = t('Servicio activo');
	    $status2 = t('Activo');
	  }
	  
	  elseif ($status == 'MO') {
	    $status = t('Corte por Mora');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'SR') {
	    $status = t('Corte Saliente por deuda');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'LI') {
	    $status = t('Corte Saliente por Limite');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'LL') {
	    $status = t('Corte saliente por limite (larga distancia)');
	    $status2 = t('Suspendido');
	  }
	  elseif ($status == 'SP') {
	    $status = t('Corte Saliente a pedido');
	    $status2 = t('Suspendido');
	  }
	  else {
	    $status = t('No Activo');
	    $status2 = t('Inactivo');
	  }
	
		$aStatus[]=$status;
    $aStatus[]=$status2;
 
		return $aStatus;
	}
}