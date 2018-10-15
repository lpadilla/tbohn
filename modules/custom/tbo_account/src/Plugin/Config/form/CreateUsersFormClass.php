<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_account\Form\CreateUsersForm;
use Drupal\tbo_entities\Entity\CompanyUserRelations;
use Drupal\tbo_entities\Entity\InvitationAccessEntity;
use Drupal\user\Entity\User;

/**
 * Manage config a 'CreateUsersFormClass' block.
 */
class CreateUsersFormClass {

  /**
   * $service_message => Almacena la instancia del servicio de envio de mail.
   * $fixed => Valor cuando el servicio no es fijo
   * $mobile => Valor cuando el servicio no es mobile.
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
    $this->api = \Drupal::service('tbo_api.client');
    $this->repository = \Drupal::service('tbo_account.repository');
  }

  /**
   *
   */
  public function createInstance(CreateUsersForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_users';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    // Se obtienen los tipos de documento de la base de datos.
    $documents = \Drupal::service('tbo_entities.entities_service');
    $options_service = $documents->getDocumentTypes();

    $document_options = [];
    foreach ($options_service as $key => $data) {
      $document_options[$data['id']] = $data['label'];
    }

    // Get roles.
    $roles = \Drupal::entityQuery('user_role')->execute();
    $user_role = $this->user->getRoles();
    $role_option = [];
    foreach ($roles as $key => $value) {
      $entity_roles = \Drupal::entityManager()
        ->getStorage('user_role')
        ->load($key);
      $role_option[$entity_roles->get('id')] = $entity_roles->get('label');
    }
    // \Drupal::logger('$role_option')->notice(print_r($role_option, TRUE));.
    unset($role_option['authenticated'], $role_option['anonymous'], $role_option['administrator']);

    // Set var for different user type.
    if ($user_role[1] == 'administrator') {
      unset($role_option['super_admin']);
    }
    elseif ($user_role[1] == 'tigo_admin' || $user_role[1] == 'admin_company') {
      unset($role_option['super_admin'], $role_option['administrator'], $role_option['tigo_admin']);
    }
    elseif ($user_role[1] == 'admin_grupo') {
      unset($role_option);
    }

    // Get enterprises.
    $form = [];

    $form['#prefix'] = '<div class="formselect">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    $form['document_type'] = [
      '#type' => 'select',
      '#title' => t('Tipo de Documento'),
      '#empty_option' => t('Seleccione opción'),
      '#empty_value' => '',
      '#options' => $document_options,
      '#required' => TRUE,
    ];

    $form['document_number'] = [
      '#type' => 'textfield',
      '#title' => t('Número de Documento'),
      '#maxlength' => 40,
      '#required' => TRUE,
      '#tree' => FALSE,
    ];

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre Completo'),
      '#maxlength' => 300,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => t('Correo Electrónico'),
      '#maxlength' => 200,
      '#required' => TRUE,
    ];

    $form['line_number'] = [
      '#type' => 'textfield',
      '#title' => t('Número de Línea'),
      '#maxlength' => 20,
      '#required' => TRUE,
    ];

    $form['rol'] = [
      '#type' => 'select',
      '#title' => t('Perfil'),
      '#empty_option' => t('Seleccione opción'),
      '#empty_value' => '',
      '#options' => $role_option,
      '#required' => TRUE,
    ];

    $form['enterprise'] = [
      '#title' => t('Empresa'),
      '#type' => 'textfield',
      '#maxlength' => 300,
      '#attributes' => [
        'data-ng-model' => ['name'],
        'ng-init' => ["name = ''"],
        'ng-change' => ["search('name')"],
        'ng-click' => ["search('name')"],
        'ng-keydown' => ['checkKeyDown($event,' . "'name')"],
        'autocomplete' => ['off'],
        'class' => ['isautocomplete'],
      ],
      '#autocomplete' => ['TRUE'],
      '#required' => TRUE,
    ];
    $form['enterprise']['#prefix'] = '<div class="tags-wrapper"><div id="tagsList" class="tags-cloud" ng-mouseleave="closeSuggestions()">';
    $form['enterprise']['#suffix'] = '</div></div>';

    $form['enterprise_value'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'value' => ['{[{ enter_value}]}'],
      ],
    ];

    $form['button-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-wrapper-button', 'col', 'input-field', 's12'],
      ],
    ];

    $form['button-wrapper']['closet'] = [
      '#markup' => '<a href="#" data-ng-click="usersListClear()" class="modal-action modal-close create-account waves-effect waves-light btn btn-second">Cancelar</a>',
    ];

    $form['button-wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Guardar'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
      ],
    ];

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

    // Validate empty number.
    $document_type = $form_state->getValue('document_type');
    $document_number = $form_state->getValue('document_number');
    if ($document_type == '') {
      $form_state->setErrorByName('document_type', 'Debe seleccionar un tipo de documento');
    }
    if ($document_number == '') {
      $form_state->setErrorByName('document_number', 'El numero de documento no puede estar vacio');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state) {
    // Set $vars.
    $email = $form_state->getValue('email');
    $name = $form_state->getValue('full_name');
    $rol = $form_state->getValue('rol');
    $enterprise = $form_state->getValue('enterprise');
    $enterpriseId = $form_state->getValue('enterprise_value');

    try {
      $document_type = $form_state->getValue('document_type');
      $document_number = $form_state->getValue('document_number');
      // Set and create User.
      $user = User::create();
      $user->setEmail($email);
      $user->setUsername($email);
      $user->set('phone_number', $form_state->getValue('line_number'));
      $user->set('document_type', $document_type);
      $user->set('document_number', $document_number);
      $user->set('full_name', $name);
      $user->addRole($rol);
      $user->save();
      $success = TRUE;

      // Save LogCreateUser.
      $service = \Drupal::service('tbo_account.create_companies_service');
      $service->insertLogCreateUser('CreateUsersFormClass', $name, $document_type, $document_number);
    }
    catch (\Exception $e) {
      $success = FALSE;
      $form_state->setError(t('Error al guardar el usuario @usuario,Codigo: @codigo'), ['@usuario' => $name, '@codigo' => $e->getCode()]);
    }

    try {
      if ($success) {
        // Set null associated.
        $associated = NULL;
        // Get current user id.
        $uid = \Drupal::currentUser()->id();
        // Load account.
        $account = User::load($uid);
        // Validate role tigo_admin in current user.
        if (in_array('tigo_admin', $account->getRoles())) {
          $associated = $uid;
        }

        // Set $vars company user role.
        $aux_user = $this->repository->getLastUidByChange();
        $company_user_role = CompanyUserRelations::create([
          'name' => $enterprise,
          'users' => $aux_user,
          'company_id' => $enterpriseId,
          'associated_id' => $associated,
          'status' => TRUE,
        ]);

        // Create company user role.
        $company_user_role->save();

        // Create URL and HASH.
        $time = $this->repository->getUserCreatedByUid($aux_user);
        $hash = Crypt::hmacBase64(($time . $email . $this->user->id()), $rol);
        $url = $GLOBALS['base_url'] . '/invitado/' . $hash;

        // $vars to send email invitation.
        $tokens['mail_to_send'] = $email;
        $tokens['user'] = $name;
        $tokens['role'] = str_replace('_', ' ', $rol);
        $tokens['link'] = $url;
        $templates = 'new_user';

        try {
          $this->service_message->send_message($tokens, $templates);
        }
        catch (\Exception $e) {
          drupal_set_message(t('Error enviando correo de invitación a @email', [
            '@email' => $email,
          ]), 'error');
        }

        try {
          $sms_message = t('Hola @username, se ha creado una cuenta para usted, con los siguientes privilegios: @rolesasignados, puede iniciar session haciendo clic en @urlsite.', ['@username' => $tokens['user'], '@rolesasignados' => $tokens['role'], '@urlsite' => $tokens['link']]);
          $sms_message_thx = t('Gracias');

          $params['query'] = [
            'from' => '85573',
            'to' => $form_state->getValue('line_number'),
            'text' => $sms_message . ' ' . $sms_message_thx,
          ];

          $sendSms = $this->api->sendSMS($params);
        }
        catch (\Exception $e) {
          // Get message.
          $message = $sms_message . ' ' . $sms_message_thx;
          // Save action.
          $service = \Drupal::service('tbo_core.audit_log_service');
          $service->loadName();
          // Create array data[].
          $data = [
            'event_type' => t('sms'),
            'description' => t('Error en el envio del SMS'),
            'details' => t("Usuario @username presento error al enviar el mensaje a @phone con el mensaje @message", [
              '@username' => $service->getName(),
              '@phone' => $form_state->getValue('line_number'),
              '@message' => $message,
            ]),
          ];

          // Save audit log.
          $service->insertGenericLog($data);
        }

        // Invitation Entity.
        $invitation = InvitationAccessEntity::create();
        $invitation->set('user_id', $aux_user);
        $invitation->set('user_name', $name);
        $invitation->set('company_id', $enterpriseId);
        $invitation->set('mail', $email);
        $invitation->set('token', $hash);
        $invitation->set('created', $time);
        $invitation->save();

        // Save log.
        $service_log = \Drupal::service('tbo_core.audit_log_service');
        $service_log->loadName();

        // Create array data[].
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

        // Save audit log.
        $service_log->insertGenericLog($data);
      }

    }
    catch (\Exception $e) {
      drupal_set_message(t('Error al guardar el usuario @user en la compañia, error @error', [
        '@user' => $name,
        '@error' => $e->getMessage(),
      ]), 'error');
    }

    drupal_set_message(t('Se ha creado un nuevo usuario exitosamente'), 'status');
  }

  /**
   * Validate if the company exists in the system.
   *
   * @param $id
   *
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
