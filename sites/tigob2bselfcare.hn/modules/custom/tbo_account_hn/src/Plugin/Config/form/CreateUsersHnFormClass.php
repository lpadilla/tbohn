<?php

namespace Drupal\tbo_account_hn\Plugin\Config\form;

use Behat\Mink\Exception\Exception;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_account_hn\Form\CreateUsersHnForm;
use Drupal\tbo_entities\Entity\CompanyUserRelations;
use Drupal\tbo_entities\Entity\InvitationAccessEntity;
use Drupal\user\Entity\User;

/**
 * Manage config a 'CreateUsersHnFormClass' block.
 */
class CreateUsersHnFormClass {
	
  /**
   * $service_message => Almacena la instancia del servicio de envio de mail.
   * $fixed => Valor cuando el servicio no es fijo
   * $mobile => Valor cuando el servicio no es mobile
   */
  protected $api;
  protected $instance;
  protected $repository;
  protected $service_message;
  protected $user;

  /**
   * AutoCreateAccountFormClass constructor.
   */
  public function __construct() {
    $this->user = \Drupal::currentUser();
    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->api = \Drupal::service('tbo_api_hn.client');
    $this->repository = \Drupal::service('tbo_account.repository');
  }

  public function createInstance(CreateUsersHnForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_users_hn';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    //Se obtienen los tipos de documento de la base de datos
    $documents = \Drupal::service('tbo_entities.entities_service');
    $options_service = $documents->getDocumentTypes();

    $document_options = [];
    foreach ($options_service as $key => $data) {
      $document_options[$data['id']] = $data['label'];
    }

    //Get roles
    $roles = \Drupal::entityQuery('user_role')->execute();
    $user_role = $this->user->getRoles();
    $role_option = array();
    foreach($roles as $key => $value){
      $entity_roles = \Drupal::entityManager()
        ->getStorage('user_role')
        ->load($key);
        if($entity_roles->get('id')!="admin_group"){
          $role_option[$entity_roles->get('id')] = $entity_roles->get('label');
        }
      
    }
    
    unset($role_option['authenticated'], $role_option['anonymous'], $role_option['administrator']);
    

    //Set var for different user type
    if($user_role[1] == 'administrator'){
      unset($role_option['super_admin']);
    } else if ($user_role[1] == 'tigo_admin' || $user_role[1] == 'admin_company'){
      unset($role_option['super_admin'], $role_option['administrator'], $role_option['tigo_admin']);
    } else if($user_role[1] == 'admin_grupo'){
      unset($role_option);
    }
		
		if($user_role[1] == 'super_admin'){
      unset($role_option['admin_company']);
    }
    
    //Get enterprises

    $form = array();

    $form['#prefix'] = '<div class="formselect">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    $form['document_type'] = array(
      '#type' => 'select',
      '#title' => t('Documento'),
      '#empty_option' => t('Seleccione opción'),
      '#empty_value' => '',
      '#options' => $document_options,
      '#required' => TRUE
    );

    $form['document_number'] = array(
      '#type' => 'textfield',
      '#title' => t(''),
      '#placeholder' => t('Número'),
      '#maxlength' => 40,
      '#required' => TRUE,
      '#tree' => FALSE
    );

    $form['full_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Nombre Completo'),
      '#placeholder' => t('Nombres y/o Apellidos'),
      '#maxlength' => 300,
      '#required' => TRUE
    );

    $form['email'] = array(
      '#type' => 'email',
      '#title' => t('Correo Electrónico'),
      '#placeholder' => t('ejemplo@tigo.com'),
      '#maxlength' => 200,
      '#required' => TRUE
    );

    $form['rol'] = array(
      '#type' => 'select',
      '#title' => t('Rol'),
      '#empty_option' => t('Seleccione opción'),
      '#empty_value' => '',
      '#options' => $role_option,
      '#required' => TRUE
    );

    $form['line_number'] = array(
      '#type' => 'textfield',
      '#title' => t('Número de línea (MSISDN)'),
      '#placeholder' => t(''),
      '#maxlength' => 20,
      '#required' => TRUE
    );

  
    $form['enterprise'] = array(
      '#title' => t('Empresa'),
      '#type' => 'textfield',
      '#placeholder' => t('Nombre de la empresa'),
      '#maxlength' => 300,
      '#attributes' => array(
        'data-ng-model' => ['name'],
        'ng-init' => ["name = ''"],
        'ng-change' => ["search('name')"],
        'ng-click' => ["search('name')"],
        'ng-keydown' => ['checkKeyDown($event,'. "'name')"],
        'autocomplete'=>['off'],
        'class' => ['isautocomplete'],
      ),
      '#autocomplete' => ['TRUE'],
      '#required' => TRUE,
    );
    $form['enterprise']['#prefix'] = '<div class="tags-wrapper"><div id="tagsList" class="tags-cloud" ng-mouseleave="closeSuggestions()">';
    $form['enterprise']['#suffix'] = '</div></div>';

    $form['enterprise_value'] = array(
      '#type' => 'hidden',
      '#attributes' => array(
        'value' => ['{[{ enter_value}]}'],
      ),
    );

    $form['button-wrapper'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => ['form-wrapper-button','col','input-field','s12'],
      ),
    );

    $form['button-wrapper']['closet'] = array(
      '#markup' => '<a href="#" data-ng-click="usersListClear()" class="modal-action modal-close create-account waves-effect waves-light btn btn-second">Cancelar</a>',
    );

    $form['button-wrapper']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Guardar'),
      '#attributes' => array(
        'class' => ['btn', 'btn-primary'],
      ),
    );

    $form['#attributes']['class'] = ['create-user'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {
  	
    $email = $form_state->getValue('email');
    $validate_email = $this->repository->getUserByEmail($email);

    if (!preg_match("#^([a-z0-9])+([a-z0-9\._-])*@([a-z0-9_-])+([a-z0-9\._-]+)+([\.])+([a-z]+)+$#i", $email)) {
      $form_state->setErrorByName('email', t('El correo electronico no tiene el formato correcto'));
    }
    else {
      if ($validate_email) {
        $form_state->setErrorByName('email', "La dirección de correo electronico $email ya se encuentra en uso.");
      }
    }

    $valEnterprise = $this->_validateEnterprises($form_state->getValue('enterprise_value'));
    if ($form_state->getValue('enterprise_value') == '' || empty($valEnterprise)) {
      $form_state->setErrorByName('enterprise', 'La empresa no se encuentra registrada en el sistema');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //la funcion nativa de drupal para validar email no funciona... se usa esta de php en su lugar
		  $form_state->setErrorByName('email', "La dirección de correo electronico no es valida.");
		}
	  
    $line_number = $form_state->getValue('line_number');
    
    $params['query'] = [
      'msisdn' => $line_number,
      'apikey' => '41541e9c127b5e1835aa195453ab3f9f',
    ];
    
    $params['tokens'] = [
      'msisdn' => $line_number,
      'apikey' => '41541e9c127b5e1835aa195453ab3f9f',
    ];
    
    //Chequeo de numero de linea (msisdn) valido via WS
     $serviceGCAGI = true;
    
    if ($serviceGCAGI == FALSE) {
      $form_state->setErrorByName('line_number', "El número de línea $line_number no es valido.");
    }
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state) {
  	
    //Set $vars
    $email = $form_state->getValue('email');
    $name = $form_state->getValue('full_name');
    $rol = $form_state->getValue('rol');
    $enterprise = $form_state->getValue('enterprise');
    $enterpriseId = $form_state->getValue('enterprise_value');
  	$line_number =   $form_state->getValue('line_number');
  
    try {

      //Set and create User
      $user = User::create();
      $user->setEmail($email);
      $user->setUsername($email);
      $user->set('phone_number', $form_state->getValue('line_number'));
      $user->set('document_type', $form_state->getValue('document_type'));
      $user->set('document_number', $form_state->getValue('document_number'));
      $user->set('full_name', $name);
      $user->addRole($rol);
      $user->save();
      $success = TRUE;
    }
    catch (Exception $e) {
      $success = FALSE;
      $form_state->setError(t('Error al guardar el usuario @usuario,Codigo: @codigo'), array('@usuario' => $name, '@codigo' => $e->getCode()));
    }

    try {
      if ($success) {
        //Set null associated
        $associated = NULL;
        //Get current user id
        $uid = \Drupal::currentUser()->id();
        //Load account
        $account = User::load($uid);
        //Validate role tigo_admin in current user
        if (in_array('tigo_admin', $account->getRoles())) {
          $associated = $uid;
        }      
      
         //If logged user is a Super Admin and the user created is a tigo_admin then this fixes the problem of the CompanyUserRelation
        if (empty($associated)) {
        
	        if ($rol == 'tigo_admin') {
	          $lastUserCreated = $this->repository->getLastUid();
	          
	          if (isset($lastUserCreated)){
	          	$associated = $lastUserCreated;
	          	
	          }
	        }
	        
        }
        
        
        $entityCUR = null;
        if (($rol == 'tigo_admin') && (!empty($associated)) ) {         
	       
	        $queryC = \Drupal::entityQuery('company_user_relations')
					    ->condition('company_id', $enterpriseId);
					    
					$entities = $queryC->execute();

					$idEC = current($entities);
					
					if ( !empty($entities) ){
						$entityCUR = entity_load('company_user_relations', $idEC);
					}
					
				}
				
				if ( !empty($entityCUR) ){
					$entityCUR->set("associated_id", $associated); //the entity exists and will be updated
					$entityCUR->save();
					
					$aux_user = $this->repository->getLastUidByChange();
				
				} else {
					//set $vars company user role
	        $aux_user = $this->repository->getLastUidByChange();
	        $company_user_role = CompanyUserRelations::create([
	          'name' => $enterprise,
	          'users' => $aux_user,
	          'company_id' => $enterpriseId,
	          'associated_id' => $associated,
	          'status' => TRUE
	        ]);
	        
	        //create company user role
        	$company_user_role->save();
				
				}		

        //Create URL and HASH
        $time = $this->repository->getUserCreatedByUid($aux_user);
        $hash = Crypt::hmacBase64(($time . $email . $this->user->id()), $rol);
        $url = $GLOBALS['base_url'] . '/invitado/' . $hash;

        //$vars to send email invitation
        $tokens['mail_to_send'] = $email;
        $tokens['user'] = $name;
        
        $tokens['role'] = $rol;
        
        $tokens['link'] = $url;
        $templates = 'new_user';

        try {
					//Sending email
          $this->service_message->send_message($tokens, $templates);
          
        } catch (Exception $e) {
          $field_email = 'email';
          $form_state->setError($field_email, "Error enviando correo de invitación a $email");
        }
        
        try {
        	//Sending SMS //AQUI DEBERIA TOMAR EL TEMPLATE SMS DE CONFIGURACION
          $sms_message = t('Hola @username, se ha creado una cuenta para usted, con los siguientes privilegios: @rolesasignados, puede iniciar session haciendo clic en @urlsite.', array('@username' => $tokens['user'], '@rolesasignados' => $tokens['role'], '@urlsite' => $tokens['link']));
          $sms_message_thx = t('Gracias');

          $params['query'] = [
            'from' => 'Tigo',
            'to' => $form_state->getValue('line_number'),
            'text' => $sms_message . ' ' . $sms_message_thx,
          ];
          
          
          
        } catch (Exception $e) {
          $field_line_number = 'line_number'; 
          $form_state->setError($field_line_number, "Error enviando SMS de invitación a $line_number");
        }

        //Invitation Entity
        $invitation = InvitationAccessEntity::create();
        $invitation->set('user_id', $aux_user);
        $invitation->set('user_name', $name);
        $invitation->set('company_id', $enterpriseId);
        $invitation->set('mail', $email);
        $invitation->set('token', $hash);
        $invitation->set('created', $time);
        $invitation->save();

        //Save log
        $service_log = \Drupal::service('tbo_core.audit_log_service');
        $service_log->loadName();

        //Create array data[]
        $user_rol = $this->user->getRoles()[1];
        $company_name = isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '';
        $company_document = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
        $company_segment = isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '';

        if ($user_rol == 'super_admin' || $user_rol == 'administrator') {
          $company_name = '';
          $company_document = '';
          $company_segment = '';
        }

        $data = [
          'companyName' => $company_name,
          'companyDocument' => $company_document,
          'companySegment' => $company_segment,
          'event_type' => 'Cuenta',
          'description' => t('Registro Nuevo usuario'),
          'details' => "Registro nuevo usuario con la información Nombre: $name, Correo electronico: $email, Roles: $rol",
        ];

        //Save audit log
        $service_log->insertGenericLog($data);
        
      }

    }
    catch (Exception $e) {
      $form_state->setError(t('Error al guardar el usuario @usuario en la compañia, error @error'), array('@usuario' => $name, '@error' => $e->getMessage()));
    }

    drupal_set_message(t('Se ha creado un nuevo usuario exitosamente'), 'status');
  }

  /**
   * validate if the company exists in the system.
   *
   * @param $id
   * @return mixed
   */
  public function _validateEnterprises($id) {
    $response = $this->repository->getCompanyToCompanyId($id);

    foreach ($response as $key => $value) {
      $response = json_decode(json_encode($value), TRUE);
    }

    return $response;
  }

}