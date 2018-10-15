<?php

namespace Drupal\tbo_simcard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\tbo_api_hn\TboApiHnClient;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\tbo_entities\Entity\InvitationAccessEntity;

use Behat\Mink\Exception\Exception;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;

/**
 * Class ChangeSimcardForm.
 */
class ChangeSimcardForm extends FormBase {

  /**
   * Drupal\tbo_api_hn\TboApiHnClient definition.
   *
   * @var \Drupal\tbo_api_hn\TboApiHnClient
   */
  protected $tboApiHnClient;
  /**
   * Constructs a new ChangeSimcardForm object.
   */

  public function __construct(
    TboApiHnClient $tbo_api_hn_client
  ) {
    $this->tboApiHnClient = $tbo_api_hn_client;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tbo_api_hn.client')
    );
  }

  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'change_simcard_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$config=null) {
    $form['simcard_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nuevo número de SIM'),
      '#placeholder' => t('Número de SIM'),
      '#maxlength' => 40,
      '#size' => 40,
    ];

    if ($config['show_simcard_owner']){
      $form['owner_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Nombre del Portador'),
        '#placeholder' => t('Nombre'),
        '#maxlength' => 140,
        '#size' => 140,
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Siguiente'),
      '#attributes'=>[
        'class'=>[
          'modal-trigger','btn', 'btn-primary','sim-continue',
        ],
        'data-target'=>[
          'modalConfirm'
        ]
      ],
      '#prefix' => '<div class="button-simcard-area" id="button-simcard-area" >',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $sim_number = $form_state->getValue('simcard_number');
    
    if($sim_number==""){
      $form_state->setErrorByName('simcard_number', t('El valor de número simcard está vacio'));
    }
    
    if(!is_numeric($sim_number)) {
      $form_state->setErrorByName('simcard_number', t('El valor de número simcard debe estar compuesto solo por numeros'));
    }
  
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $sim_number = $form_state->getValue('simcard_number');

    $repository = \Drupal::service('tbo_account.repository');    
    $segment = 'segmento';
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    // Data log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $data_log = [];
    
    $params = [
      'client' => [
        'id' => $_SESSION['company']['company_code'],
        'idType' => 'clientId',
      ],
      'newSim' => [
        'iccid' => $sim_number,
      ],
    ];

    $jsonBody = json_encode($params);

    $params = [
      'tokens' => [
        'phone' => $_SESSION['sendDetail']['invoice']['msisdn'], 
      ],
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'body' => $jsonBody,
    ];

    
    try{
      $ws_response = $this->tboApiHnClient->putSimCard($params); 
      
			if (!empty($ws_response)) {
				
				if ( ($ws_response->tigoResponse->status == 200) & ($ws_response->tigoResponse->response->message == 'Transacción Exitosa' ) ){	 //revisar!! RVS 20171102
					
					drupal_set_message(t('Cambiar SIM'), 'status');
	    		drupal_set_message(t('Los cambios fueron aplicados correctamente'), 'status');
	    		
	    		$data_log = [
		        'companyName' => $_SESSION['company']['name'],
		        'companyDocument' => $_SESSION['company']['nit'],
		        'event_type' => 'SimCard',
		        'description' => 'Usuario intento cambiar de simCard ',
		        'details' => 'Usuario cambió exitosamente el número de sim a: ' . $sim_number,
		      ];
	    		
    		} else {
    
    			drupal_set_message('Cambiar SIM ', 'error');
		      drupal_set_message('Ha ocurrido un problema al intentar aplicar los cambios. Por favor, inténtelo nuevamente: ' . $ws_response->tigoResponse->response->message, 'error');
		      
		      $data_log = [
		        'companyName' => $_SESSION['company']['name'],
		        'companyDocument' => $_SESSION['company']['nit'],
		        'event_type' => 'SimCard',
		        'description' => 'Usuario intento cambiar de simCard ',
		        'details' => 'Error cuando el usuario intento cambiar el número de sim a: ' .$sim_number,
		      ];
      
    		}
			
			}
      
      //Save audit log
      $service_log->insertGenericLog($data_log);
      
    }catch (\Exception $exception) {
      \Drupal::logger('changeSimcard')->error('Error: ' . $exception->getMessage());
       //Save audit log
      drupal_set_message('Cambiar SIM ', 'error');
      drupal_set_message('Ha ocurrido un problema al intentar aplicar los cambios. Por favor, inténtelo nuevamente: ' . $exception->getMessage(), 'error');
        
      $current_path = \Drupal::service('path.current')->getPath();     
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
      
    }
    
    $redirect_path = "/cambiar-simcard";
    $url = url::fromUserInput($redirect_path);
    
    $form_state->setRedirectUrl($url);

  }

}
