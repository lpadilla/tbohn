<?php

namespace Drupal\tbo_support_bo\Plugin\Config\Block;

use Drupal\tbo_support_bo\Plugin\Block\SupportBoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;


/**
 * Manage config a 'SupportBoBlockClass' block.
 */
class SupportBoBlockClass {
	protected $instance;
	protected $configuration;

  /**
   * @param SupportBoBlock $instance
   * @param $config
  */ 
  public function setConfig(SupportBoBlock &$instance, &$config) {
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
          'call' => [            
            'call_title' => "Call Center",
            'phone_call_number' => "800170707, *611",
            'schedule_call_time' => "24 Horas",

            'detail' => [
              'phone_call_show' => ['title' => t('Teléfono'), 'label' => t('Teléfono'), 'service_field' => 'phone_call_show', 'show' => 1],
              'schedule_call_show' => ['title' => t('Horario'), 'label' => t('Horario'), 'service_field' => 'schedule_call_show', 'show' => 1],
            ],
          ],
          'agent' => [
            'agent_title' => 'Ejecutivo Comercial',
            'phone_agent_number' => '*121',
            'schedule_agent_time' => '24 Horas',
            'email_agent' => 'servicios@tigo.net.bo',
            
            'detail' => [              
              'phone_agent_show' => ['title' => t('Teléfono'), 'label' => t('Teléfono'), 'service_field' => 'phone_agent_show', 'show' => 1],
              'schedule_agent_show' => ['title' => t('Horario'), 'label' => t('Horario'), 'service_field' => 'schedule_agent_show', 'show' => 1],
              'email_agent_show' => ['title' => t('Correo'), 'label' => t('Correo'), 'service_field' => 'email_agent_show', 'show' => 1],
              'send_mail' => ['title' => t('Enviar Email'), 'label' => t('Enviar Email'), 'service_field' => 'send_mail', 'show' => 1],
              'name_show' => ['title' => t('Nombre'), 'label' => t('Nombre'), 'service_field' => 'name_show', 'show' => 1],
            ],
          ],
        ]
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    // START FORMULARIO PARA CALL CENTER

      //CONFIGURACION PARA EL ENCABEZADO DEL BLOQUE
        $form['others']['config']['call'] = array(
          '#type' => 'details',
          '#title' => t('Configuraciones de Call Center'),
          '#open' => TRUE,
        );

      //CONFIGURACION DE TITULO DE CALL CENTER
        $form['others']['config']['call']['call_title'] = [
          '#type' => 'textfield',
          '#title' => t('Titulo'),
          '#default_value' => $this->configuration['others']['config']['call']['call_title'],
          '#description' => t('Digite el titulo' ),
          '#required' => TRUE,
        ];

      //CONFIGURACION DE TELEFONO DE CALL CENTER
        $form['others']['config']['call']['phone_call_number'] = [
          '#type' => 'textfield',
          '#title' => t('Número telefonico'),
          '#default_value' => $this->configuration['others']['config']['call']['phone_call_number'],
          '#description' => t('Digite el número telefonico' ),
          '#required' => TRUE,
        ];

      //CONFIGURACION DEL HORARIO DEL CALL CENTER
        $form['others']['config']['call']['schedule_call_time'] = [
          '#type' => 'textfield',
          '#title' => t('Horario'),
          '#default_value' => $this->configuration['others']['config']['call']['schedule_call_time'],
          '#description' => t('Digite el horario' ),
          '#required' => TRUE,
        ];      

      //CONFIGURACION DETALLE DEL CALL CENTER (LA TABLA)
        $form['others']['config']['call']['detail'] = array(
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

      //SE OBTIENEN LOS DATOS DESDE LA FUNCION DE CONFIGURACION
      $datos_call = $this->configuration['others']['config']['call']['detail'];

      //CONFIGURACION DE PHONE CALL
        $form['others']['config']['call']['detail']['phone_call_show']['title'] = array(
          '#plain_text' => $datos_call['phone_call_show']['title'],
        );

        $form['others']['config']['call']['detail']['phone_call_show']['label'] = array(          
          '#type' => 'textfield',
          '#default_value' => $datos_call['phone_call_show']['label'],
        );

        $form['others']['config']['call']['detail']['phone_call_show']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_call['phone_call_show']['show'],
        );
      //END CONFIGURACION DE PHONE CALL

      //CONFIGURACION DE SCHEDULE CALL
        $form['others']['config']['call']['detail']['schedule_call_show']['title'] = array(
          '#plain_text' => $datos_call['schedule_call_show']['title'],
        );

        $form['others']['config']['call']['detail']['schedule_call_show']['label'] = array(         
          '#type' => 'textfield',
          '#default_value' => $datos_call['schedule_call_show']['label'],
        );

        $form['others']['config']['call']['detail']['schedule_call_show']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_call['schedule_call_show']['show'],
        );
      //END CONFIGURACION DE SCHEDULE CALL
    //END FORMULARIO PARA CALL CENTER

    // START FORMULARIO PARA AGENTES DE SOPORTE

      //CONFIGURACION PARA EL ENCABEZADO DEL BLOQUE
        $form['others']['config']['agent'] = array(
          '#type' => 'details',
          '#title' => t('Configuraciones de Agentes de Soporte'),
          '#open' => TRUE,
        );

      //CONFIGURACION DE TITULO DE AGENTES DE SOPORTE
        $form['others']['config']['agent']['agent_title'] = [
          '#type' => 'textfield',
          '#title' => t('Titulo'),
          '#default_value' => $this->configuration['others']['config']['agent']['agent_title'],
          '#description' => t('Digite el titulo' ),
          '#required' => TRUE,
        ];

      //CONFIGURACION DE TELEFONO DE AGENTES DE SOPORTE
        $form['others']['config']['agent']['phone_agent_number'] = [
          '#type' => 'textfield',
          '#title' => t('SOLO PARA SEGMENT SMALL O MICRO: Número telefónico'),
          '#default_value' => $this->configuration['others']['config']['agent']['phone_agent_number'],
          '#description' => t('Digite el número telefónico' ),
          '#required' => TRUE,
        ];

      //CONFIGURACION DEL HORARIO DE AGENTES DE SOPORTE
        $form['others']['config']['agent']['schedule_agent_time'] = [
          '#type' => 'textfield',
          '#title' => t('SOLO PARA SEGMENT SMALL O MICRO: Horario'),
          '#default_value' => $this->configuration['others']['config']['agent']['schedule_agent_time'],
          '#description' => t('Digite el horario' ),
          '#required' => TRUE,
        ];

      //CONFIGURACION DEL EMAIL DE AGENTES DE SOPORTE
        $form['others']['config']['agent']['email_agent'] = [
          '#type' => 'textfield',
          '#title' => t('SOLO PARA SEGMENT SMALL O MICRO: Correo'),
          '#default_value' => $this->configuration['others']['config']['agent']['email_agent'],
          '#description' => t('Digite el correo' ),
          '#required' => TRUE,
        ];

      //CONFIGURACION DETALLE DE AGENTES DE SOPORTE (LA TABLA)
        $form['others']['config']['agent']['detail'] = array(
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

      $datos_agent = $this->configuration['others']['config']['agent']['detail'];
           
      //CONFIGURACION DE NAME AGENT
        $form['others']['config']['agent']['detail']['name_show']['title'] = array(
          '#plain_text' => $datos_agent['name_show']['title'],
        );

        $form['others']['config']['agent']['detail']['name_show']['label'] = array(          
          '#type' => 'textfield',
          '#default_value' => $datos_agent['name_show']['label'],
        );

        $form['others']['config']['agent']['detail']['name_show']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_agent['name_show']['show'],
        );
      //END CONFIGURACION DE NAME AGENT
          
      //CONFIGURACION DE PHONE AGENT
        $form['others']['config']['agent']['detail']['phone_agent_show']['title'] = array(
          '#plain_text' => $datos_agent['phone_agent_show']['title'],
        );

        $form['others']['config']['agent']['detail']['phone_agent_show']['label'] = array(          
          '#type' => 'textfield',
          '#default_value' => $datos_agent['phone_agent_show']['label'],
        );

        $form['others']['config']['agent']['detail']['phone_agent_show']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_agent['phone_agent_show']['show'],
        );
      //END CONFIGURACION DE PHONE AGENT
      
      //CONFIGURACION DE SCHEDULE AGENT
        $form['others']['config']['agent']['detail']['schedule_agent_show']['title'] = array(
          '#plain_text' => $datos_agent['schedule_agent_show']['title'],
        );

        $form['others']['config']['agent']['detail']['schedule_agent_show']['label'] = array(          
          '#type' => 'textfield',
          '#default_value' => $datos_agent['schedule_agent_show']['label'],
        );

        $form['others']['config']['agent']['detail']['schedule_agent_show']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_agent['schedule_agent_show']['show'],
        );
      //END CONFIGURACION DE SCHEDULE AGENT

      //CONFIGURACION DE EMAIL AGENT
        $form['others']['config']['agent']['detail']['email_agent_show']['title'] = array(
          '#plain_text' => $datos_agent['email_agent_show']['title'],
        );

        $form['others']['config']['agent']['detail']['email_agent_show']['label'] = array(          
          '#type' => 'textfield',
          '#default_value' => $datos_agent['email_agent_show']['label'],
        );

        $form['others']['config']['agent']['detail']['email_agent_show']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_agent['email_agent_show']['show'],
        );
      //END CONFIGURACION DE EMAIL AGENT

      //CONFIGURACION DE ENVIAR CORREO
        $form['others']['config']['agent']['detail']['send_mail']['title'] = array(
          '#plain_text' => $datos_agent['send_mail']['title'],
        );

        $form['others']['config']['agent']['detail']['send_mail']['label'] = array(          
          '#type' => 'textfield',
          '#default_value' => $datos_agent['send_mail']['label'],
        );

        $form['others']['config']['agent']['detail']['send_mail']['show'] = array(
          '#type' => 'checkbox',
          '#default_value' => $datos_agent['send_mail']['show'],
        );
      //END CONFIGURACION DE ENVIAR CORREO


    // END FORMULARIO PARA AGENTES DE SOPORTE

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
  public function build(SupportBoBlock &$instance, &$config){

    $this->api = \Drupal::service('tbo_api_bo.client'); 

    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('config_name', 'SupportBlock');
    $this->instance->setValue('directive', 'data-ng-invoice-delivery');
    $this->instance->setValue('class', 'support');

    //variables de call center
    $call_title = $this->configuration['others']['config']['call']['call_title'];
    $phone_call_number_str = $this->configuration['others']['config']['call']['phone_call_number'];
    $phone_call_number = explode(",", $phone_call_number_str);

    $schedule_call_time_str = $this->configuration['others']['config']['call']['schedule_call_time'];
    $schedule_call_time = explode(",", $schedule_call_time_str);

    $data_call = $this->configuration['others']['config']['call']['detail'];
    $phone_call_show = array(
      "label"=>$data_call['phone_call_show']['label'], 
      "show"=>$data_call['phone_call_show']['show']
    );
    $schedule_call_show = array(
      "label"=>$data_call['schedule_call_show']['label'], 
      "show"=>$data_call['schedule_call_show']['show']
    );

    //variables de agentes de servicio
    $agent_title = $this->configuration['others']['config']['agent']['agent_title'];
    $phone_agent_number_str = $this->configuration['others']['config']['agent']['phone_agent_number'];
    $phone_agent_number = explode(",", $phone_agent_number_str);
    
    $schedule_agent_time_str = $this->configuration['others']['config']['agent']['schedule_agent_time'];
    $schedule_agent_time = explode(",", $schedule_agent_time_str);

    $email_agent =  $this->configuration['others']['config']['agent']['email_agent'];

    $data_agent = $this->configuration['others']['config']['agent']['detail'];
    $phone_agent_show = array(
      "label"=>$data_agent['phone_agent_show']['label'], 
      "show"=>$data_agent['phone_agent_show']['show']
    );
    $schedule_agent_show = array(
      "label"=>$data_agent['schedule_agent_show']['label'], 
      "show"=>$data_agent['schedule_agent_show']['show']
    );
    $email_agent_show = array(
      "label"=>$data_agent['email_agent_show']['label'], 
      "show"=>$data_agent['email_agent_show']['show']
    );
    $send_mail = array(
      "label"=>$data_agent['send_mail']['label'], 
      "show"=>$data_agent['send_mail']['show']
    );
    $name_show = array(
      "label"=>$data_agent['name_show']['label'], 
      "show"=>$data_agent['name_show']['show']
    );

    $modal = [
      'data' => $this->configuration['others_display'],
      'environment' => $_SESSION['environment'],
    ];

    //set title
    $title = t("AGENTES DE SOPORTE");

    //SupportsBoForm
    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_support_bo\Form\SupportsBoForm',$this->configuration);

    // Parameters for service.
    $params1['tokens'] = [
      'documentNumber' => $_SESSION['company']['nit'],
      'offset' => '1',
      'limit' => '1',
    ];

    $params1['query'] =  [
      'offset' => '1',
      'limit' => '1',
    ];
    
    //llamada del servicio
    $resp = $this->api->getCustomerGeneralInfoByCustomerIdBySupportAgent($params1); 
    
    $msisdn = $resp->Envelope->Body->getClientAccountGeneralInfoMobileResponse->contracts->contract[0]->accounts->AssetType->msisdn;
    
    if ( empty($msisdn) ){
    	$msisdn = $resp->Envelope->Body->getClientAccountGeneralInfoMobileResponse->contracts->contract[0]->accounts->AssetType[0]->msisdn;
    }
    
    if ( empty($msisdn) ){
    
   
			
			$message = "call to getCustomerGeneralInfoByCustomerIdBySupportAgent NOT successful";
			\Drupal::logger('tbo_support_bo')->error($message);
		  
		  $segment = "";
		  $name_agent = "";

		  $email_agent = "";
		  
		  $phone_agent_number_str = "";
		  $phone_agent_number = "";
		  
		  $schedule_agent_time_str = t("24 Horas");
		  $schedule_agent_time = "";

    
    } else {
    
	    /* Section Consulta de Saldo */
	    $params['query'] = [
	      //'msisdn' => $msisdn,
	    ];
	    
	    $params['tokens'] = [
	      'msisdn' => $msisdn,      
	    ];   
	    
	    $segment = "";
	    
	    try{
        //llamada del servicio
	      $response = $this->api->getCustomerSegmentLimitContract($params);      
	      
	      $segment = trim($response->corporateSegment);
	          
	      if($segment == 'Large' || $segment == 'Medium'){
	        $name_agent = $response->accountExecutiveCorporate;

	        $email_agent = $response->emailCorporate;
	        
	        $phone_agent_number_str = $response->phoneCorporate;
	        $phone_agent_number = explode(",", $phone_agent_number_str);
	        
	        $schedule_agent_time_str = t("24 Horas");
	        $schedule_agent_time = explode(",", $schedule_agent_time_str);     
	      }
	    } catch (\Exception $e) {
	      // Se guarda el log de auditoria $event_type, $description, $details = NULL.
	      

	      return new ResourceResponse(UtilMessage::getMessage($e));
	    }
	  }
    
    $this->instance->cardSaveAuditLog('Support_Agent', 'Usuario accede a agentes de soporte', 'Se accede a agentes de soporte');

    $build = array(
      '#theme' => 'supports_bo',
      '#directive' => $this->instance->getValue('directive'),
      '#class' => $this->instance->getValue('class'),
      '#form'=> $form,
      '#formName' => 'supports-form',
      '#modal' => $modal,
      '#msisdn' => $msisdn,
      '#name_agent' => $name_agent,
      '#segment' => $segment,
      '#call_title' => $call_title,
      '#phone_call_number' => $phone_call_number,
      '#schedule_call_time' => $schedule_call_time,
      '#phone_call_show' => $phone_call_show,
      '#schedule_call_show' => $schedule_call_show,
      '#agent_title' => $agent_title,
      '#phone_agent_number' => $phone_agent_number,
      '#schedule_agent_time' => $schedule_agent_time,
      '#name_show' => $name_show,
      '#phone_agent_show' => $phone_agent_show,
      '#schedule_agent_show' => $schedule_agent_show,
      '#email_agent_show' => $email_agent_show,
      '#email_agent' => $email_agent,
      '#send_mail' => $send_mail,
      '#title' => $title,
      '#attached' => array(
        'library' => array(
          'tbo_support_bo/support',
        ),
      ),      
    );
   
    $this->instance->setValue('build', $build);

    return $this->instance->getValue('build');
  }

}