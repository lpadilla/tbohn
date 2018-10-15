<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\user\Entity\User;
use Behat\Mink\Exception\Exception;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tbo_account\Form\CreateEnterpriseForm;
use Drupal\tbo_entities\Entity\InvitationAccessEntity;

/**
 * Manage config a 'CreateEnterpriseFormClass' block.
 */
class CreateEnterpriseFormClass {

  /**
   * $service_message => Almacena la instancia del servicio de envio de mail.
   * $fixed => Valor cuando el servicio no es fijo
   * $mobile => Valor cuando el servicio no es mobile.
   */
  protected $api;
  protected $instance;
  protected $service_message;
  protected $fixed = 'no fixed';
  protected $mobile = 'no mobile';
  protected $service_enterprise;

  /**
   * AutoCreateAccountFormClass constructor.
   */
  public function __construct() {
    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->api = \Drupal::service('tbo_api.client');
    $this->service_enterprise = \Drupal::service('tbo_account.create_companies_service');
  }

  /**
   *
   */
  public function createInstance(CreateEnterpriseForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_enterprise';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    $form['#prefix'] = '<div class="formselect jscroll" id="appcat">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    // Se obtienen los tipos de documento de la base de datos.
    $documents = \Drupal::service('tbo_entities.entities_service');
    $options_service = $documents->getDocumentTypes();


    $options = [];
    foreach ($options_service as $key => $data) {
      $options[$data['id']] = $data['label'];
    }

    $document = '';
    // Get document and number.
    if ($form_state->getValue('document_number')) {
      $document = $form_state->getValue('document_type');
    }

    if ($form_state->getValue('document_number')) {
      $number = $form_state->getValue('document_number');
    }
    else {
      $number = FALSE;
    }

    $form['document_type'] = [
      '#type' => 'select',
      '#title' => t('Tipo de documento'),
      '#empty_option' => t('Seleccione opción'),
      '#options' => $options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, '_validateCompanyNumber'],
        'wrapper' => 'content-enterprise-name',
        'event' => 'change',
      ],
    ];

    $form['document_number'] = [
      '#type' => 'textfield',
      '#title' => t('Número de documento'),
      '#maxlength' => 145,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, '_validateCompanyNumber'],
        'wrapper' => 'content-enterprise-name',
        'event' => 'change',
      ],
    ];

    $form['enterprise_name'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre de la empresa'),
      '#maxlength' => 145,
      '#attributes' => [
        'disabled' => 'disabled',
        'inactive' => 'inactive',
        'class' => ['disabled'],
      ],
      '#value' => $this->_validateService($document, $number),
      '#required' => TRUE,
      '#prefix' => '<div class="content-enterprise-name" id="content-enterprise-name">',
      '#suffix' => '</div>',
    ];

    $form['user'] = [
      '#type' => 'radios',
      '#title' => t('Usuario administrador'),
      '#maxlength' => 145,
      '#options' => [
        'create' => t('Crear nuevo usuario'),
        'associate' => t('Asociar usuario existente'),
      ],
      '#attributes' => [
        'ng-click' => ['changeAdmin($event)'],
        'ng-model' => ['adminType'],
      ],
      '#required' => TRUE,
    ];

    $form['user_fielset'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="user-form" class="hidden">',
      '#suffix' => '</div>',
    ];

    $form['user_fielset']['user_form'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="user-form-new" class="hidden">',
      '#suffix' => '</div>',
    ];

    $form['user_fielset']['user_form']['admin_name'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre Admin Empresa'),
      '#maxlength' => 300,
    ];

    $form['user_fielset']['user_form']['admin_mail'] = [
      '#type' => 'email',
      '#title' => t('Correo Electrónico'),
      '#maxlength' => 200,
    ];

    $form['user_fielset']['user_form']['admin_phone'] = [
      '#type' => 'tel',
      '#title' => t('Nro Celular'),
      '#maxlength' => 20,
    ];

    $form['user_fielset']['user_form_old'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="user-form-old" class="hidden">',
      '#suffix' => '</div>',
    ];

    $form['user_fielset']['user_form_old']['admin_mail'] = [
      '#id' => 'autocomplete_ajax',
      '#title' => t('Correo Electrónico'),
      '#type' => 'textfield',
      '#maxlength' => 200,
      '#attributes' => [
        'ng-change' => ["searchMail('mail')"],
        'data-ng-model' => ['mail'],
        'ng-keydown' => ['checkKeyDownMail($event,' . "'mail')"],
        'autocomplete' => ['off'],
        'class' => ['isautocomplete'],
        'autocomplete_ajax' => 'autocomplete_ajax',
      ],
      '#autocomplete' => ['TRUE'],
    ];
    $form['user_fielset']['user_form_old']['admin_mail']['#prefix'] = '<div class="tags-wrapper"><div id="tagsList" class="tags-cloud">';
    $form['user_fielset']['user_form_old']['admin_mail']['#suffix'] = '</div></div>';

    $form['closet'] = [
      '#markup' => '<a href="#" class="modal-close-b2b create-account waves-effect waves-light btn btn-second">Cancelar</a>',
      '#prefix' => '<div class="input-field col s12 m12 l12 form-wrapper-button">',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Siguiente'),
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['btn-primary'],
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'tbo_general/tools.tbo';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {
    $number = $form_state->getValue('document_number');
    $repository = \Drupal::service('tbo_account.repository');

    // Validate space in document number.
    $spaces = 0;
    $number = str_replace(" ", "", $number, $spaces);
    if ($spaces > 0) {
      $form_state->setErrorByName('document_number', t('El número de documento contiene espacios'));
    }

    $aux_document = $repository->getCompanyToDocumentNumber($number);

    if ($aux_document) {
      $form_state->setErrorByName('document_number', t('El número de documento de la empresa ya se encuentra registrado en el sistema'));
    }
    else {
      $document = $form_state->getValue('document_type');
      $validation = $this->_validateService($document, $number);

      if ($validation == 'Empresa no existe') {
        $form_state->setErrorByName('document_number', t('El número de documento de la empresa no existe en el sistema'));
      }

      if (!empty($form_state->getValue('user_fielset')['user_form']['admin_mail'])) {
        $aux_user_mail = $repository->getUserByEmail($form_state->getValue('user_fielset')['user_form']['admin_mail']);
        $aux_user_name = $repository->getUserByName($form_state->getValue('user_fielset')['user_form']['admin_name']);

        if ($aux_user_name || $aux_user_mail) {
          $form_state->setErrorByName('user_fielset', t('El usuario ya se encuentra registrado en el sistema'));
        }
      }
      elseif (!empty($form_state->getValue('user_fielset')['user_form_old']['admin_mail'])) {
        $aux_user = $repository->getUserByEmail($form_state->getValue('user_fielset')['user_form_old']['admin_mail']);
        if (!$aux_user) {
          $form_state->setErrorByName('user_fielset', t('El usuario no se encuentra registrado en el sistema'));
        }
      }
    }

    if ($form_state->getValue('user') == 'create') {
      if (empty($form_state->getValue('user_fielset')['user_form']['admin_name'])) {
        $form_state->setErrorByName('user_fielset][user_form][admin_name', t('Debe ingresar el nombre admin empresa del nuevo usuario'));
      }
      if (empty($form_state->getValue('user_fielset')['user_form']['admin_mail'])) {
        $form_state->setErrorByName('user_fielset][user_form][admin_mail', t('Debe ingresar el correo del nuevo usuario'));
      }
      if (empty($form_state->getValue('user_fielset')['user_form']['admin_phone'])) {
        $form_state->setErrorByName('user_fielset][user_form][admin_phone', t('Debe ingresar el telefono del nuevo usuario'));
      }
    }
    elseif ($form_state->getValue('user') == 'associate') {
      if (empty($form_state->getValue('user_fielset')['user_form_old']['admin_mail'])) {
        $form_state->setErrorByName('user_fielset][user_form_old][admin_mail', t('Debe ingresar el correo del administrador'));
      }
      else {
        $aux_user = \Drupal::service('tbo_account.repository')->getUserUidMailByEmail($form_state->getValue('user_fielset')['user_form_old']['admin_mail']);
        if (!$aux_user) {
          $form_state->setErrorByName('user_fielset][user_form_old][admin_mail', t('El correo del admin_empresa ingresado es incorrecto, el usuario esta inactivo o el usuario no es un admin_empresa valido.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state) {
    $repository = \Drupal::service('tbo_account.repository');
    // TODO temporary.
    $segment = 'segmento';
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    // Data log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    $data_log = [];

    // Get name rol.
    $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);

    $mobile = FALSE;
    $fixed = FALSE;

    try {
      if ($this->fixed == 'fixed' && $this->mobile == 'mobile') {
        $mobile = TRUE;
        $fixed = TRUE;
      }
      if ($this->fixed == 'fixed' && $this->mobile != 'mobile') {
        $fixed = TRUE;
      }
      if ($this->fixed != 'fixed' && $this->mobile == 'mobile') {
        $mobile = TRUE;
      }

      $company_data = [
        'name' => $form_state->getValue('enterprise_name'),
        'document_type' => $form_state->getValue('document_type'),
        'document_number' => $form_state->getValue('document_number'),
        'company_name' => $form_state->getValue('enterprise_name'),
        'segment' => $segment,
        'status' => TRUE,
        'fixed' => $fixed,
        'mobile' => $mobile,
      ];

      // Save company.
      $this->service_enterprise->_createCompany($company_data);
      $detailFixe = "";
      if ($fixed) {
        $config = \Drupal::config('tbo_account.autocreateformconfig');
        $method = $config->get('method');
        $detailFixe = ' La empresa se creó por el método ' . $method . '.';
      }

      // Create Audit log.
      $data_log = [
        'companyName' => '',
        'companyDocument' => '',
        'companySegment' => $segment,
        'event_type' => 'Cuenta',
        'description' => 'Usuario crea la empresa ' . $form_state->getValue('enterprise_name'),
      ];

    }
    catch (\Exception $exception) {
      drupal_set_message('Error al crear empresa, por favor verifique los datos: ' . $exception->getMessage());
      $current_path = \Drupal::service('path.current')->getPath();
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
    }

    $aux_company = $repository->getLastCompany();
    $aux_user = '';
    $associated = NULL;

    try {
      if ($form_state->getValue('user') == 'create') {
        // Get values formstate.
        $mail = $form_state->getValue('user_fielset')['user_form']['admin_mail'];
        $username = $form_state->getValue('user_fielset')['user_form']['admin_name'];
        $phone_number = $form_state->getValue('user_fielset')['user_form']['admin_phone'];
        $document_number = $form_state->getValue('document_number');
        $document_type = $form_state->getValue('document_type');
        $enterprise_name = $form_state->getValue('enterprise_name');

        $data_user = [
          'mail' => $mail,
          'username' => $mail,
          'phone_number' => $phone_number,
          'document_number' => $document_number,
          'document_type' => $document_type,
          'full_name' => $username,
        ];

        // Create user.
        $this->service_enterprise->_createUser($data_user, 'CreateEnterpriseFormClass_line_376_create_user');

        $aux_user = $repository->getLastUid();

        if (in_array('tigo_admin', $account->getRoles())) {
          $associated = $uid;
        }

        // Create relations company-user.
        $data_relation = [
          'name' => $enterprise_name,
          'users' => $aux_user,
          'company_id' => $aux_company,
          'associated_id' => $associated,
          'status' => TRUE,
        ];
        $this->service_enterprise->_CreateCompanyUserRelation($data_relation);

        // Envio de mensaje de texto de confirmacion de creacion de empresa.
        $sms_message = 'Nuevo administrador de empresa ' . $enterprise_name;
        $this->service_enterprise->_sendSms($phone_number, $sms_message, 'Crear empresa', $username, $form_state->getValue('enterprise_name'));

        /**
         * creacion del log de auditoria
         */
        $data_log['details'] = 'Usuario ' . $name . ' crea la empresa ' . $enterprise_name . ' y le asocia el admin empresa ' . $username . ' ' . $mail . '.' . $detailFixe;

        /**
         * Create register in invited table
         */

        // Create URL and HASH.
        $time = $repository->getUserCreatedByUid($aux_user);
        $hash = Crypt::hmacBase64(($time . $mail . $uid), 'admin_company');
        $url = $GLOBALS['base_url'] . '/invitado/' . $hash;

        // Invitation Entity.
        $invitation = InvitationAccessEntity::create();
        $invitation->set('user_id', $aux_user);
        $invitation->set('user_name', $mail);
        $invitation->set('company_id', $aux_company);
        $invitation->set('mail', $mail);
        $invitation->set('token', $hash);
        $invitation->set('created', $time);
        $invitation->save();

        // $vars to send email invitation.
        $tokens['mail_to_send'] = $mail;
        $tokens['user'] = $username;
        $tokens['role'] = str_replace('_', ' ', 'admin_company');
        $tokens['link'] = $url;
        $templates = 'new_user';

        try {
          // $this->service_message->send_message($tokens, $templates);.
          $sms_message = t('Hola @username, se ha creado una cuenta para usted, con los siguientes privilegios: @rolesasignados, puede iniciar session haciendo clic en @urlsite.', ['@username' => $tokens['user'], '@rolesasignados' => $tokens['role'], '@urlsite' => $tokens['link']]);
          $sms_message_thx = t('Gracias');

          $this->service_enterprise->_sendSms($phone_number, $sms_message . ' ' . $sms_message_thx, 'Crear empresa', $username, $form_state->getValue('enterprise_name'));

          /**
           * envio de correo de confirmacion de creacion de la nueva empresa
           */
          $tokens_enterprise['user'] = $username;
          $tokens_enterprise['admin'] = $name;
          $tokens_enterprise['enterprise'] = $enterprise_name;
          $tokens_enterprise['enterprise_num'] = $document_number;
          $tokens_enterprise['document'] = $document_type;
          $tokens_enterprise['admin_enterprise'] = $username;
          $tokens_enterprise['admin_mail'] = $mail;
          $tokens_enterprise['admin_phone'] = $phone_number;
          $tokens_enterprise['mail_to_send'] = $mail;
          $tokens_enterprise['link'] = $url;
          try {
            $send = $this->service_message->send_message($tokens_enterprise, 'new_enterprise');
          }
          catch (\Exception $e) {
            $service = \Drupal::service('tbo_core.audit_log_service');
            $service->loadName();
            // Create array data[].
            $data = [
              'event_type' => t('Cuenta'),
              'description' => t('Error en el envio del email'),
              'details' => 'Usuario ' . $service->getName() . ' presento error en Creacion masiva de empresas al enviar el mensaje al admin empresa ' . $username . ' de la empresa ' . $enterprise_name . ', con email ' . $mail,
            ];

            // Save audit log.
            $service->insertGenericLog($data);
          }
        }
        catch (Exception $e) {
          $field = 'document_number';
          $form_state->setError($field, t("Error enviando correo de invitación a $mail"));
        }
      }
      else {
        if ($form_state->getValue('user') == 'associate') {
          if (in_array('tigo_admin', $account->getRoles())) {
            $associated = $uid;
          }

          $aux_user = $repository->getUserUidMailByEmail($form_state->getValue('user_fielset')['user_form_old']['admin_mail']);

          // Create relations company-user.
          $company_user_role = [
            'name' => $form_state->getValue('enterprise_name'),
            'users' => $aux_user[0]->uid,
            'company_id' => $aux_company,
            'associated_id' => $associated,
            'status' => TRUE,
          ];
          $this->service_enterprise->_CreateCompanyUserRelation($company_user_role);

          // Setear el detalle del log.
          $data_log['details'] = 'Usuario ' . $name . ' crea la empresa ' . $form_state->getValue('enterprise_name') . ' y le asocia el admin empresa ' . $aux_user[0]->name . ' ' . $form_state->getValue('user_fielset')['user_form_old']['admin_mail'] . '.' . $detailFixe;

          // Generar los tokens para el envio del email.
          $tokens['user'] = $aux_user['name'];
          $tokens['enterprise'] = $form_state->getValue('enterprise_name');
          $tokens['mail_to_send'] = $form_state->getValue('user_fielset')['user_form_old']['admin_mail'];

          /**
           * envio de mensaje de texto de confirmacion de creacion de empresa
           */
          $sms_message = 'Nuevo administrador de empresa ' . $tokens['enterprise'];

          $account = User::load($aux_user[0]->uid);
          if (!empty($account->get('phone_number')->value)) {
            $phone_number = $account->get('phone_number')->value;
            $this->service_enterprise->_sendSms($phone_number, $sms_message, 'Crear empresa', $aux_user[0]->name, $form_state->getValue('enterprise_name'));
          }

          /**
           * sending confirmation mail
           */
          try {
            $send = $this->service_message->send_message($tokens, 'assing_enterprise');
          }
          catch (\Exception $e) {
            $service = \Drupal::service('tbo_core.audit_log_service');
            $service->loadName();
            // Create array data[].
            $data = [
              'event_type' => t('Cuenta'),
              'description' => t('Error en el envio del email'),
              'details' => 'Usuario ' . $service->getName() . ' presento error en Creacion masiva de empresas al enviar el mensaje al admin empresa ' . $aux_user[0]->name . ' de la empresa ' . $form_state->getValue('enterprise_name') . ', con email ' . $form_state->getValue('user_fielset')['user_form_old']['admin_mail'],
            ];

            // Save audit log.
            $service->insertGenericLog($data);
          }
        }
      }
    }
    catch (\Exception $exception) {
      // Logguer.
      \Drupal::logger('createCompany')->error('Error: ' . $exception->getMessage());

      // Set message.
      drupal_set_message('Error al crear/asignar admin empresa, por favor verifique los datos: ' . $exception->getMessage());
      $current_path = \Drupal::service('path.current')->getPath();
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
    }

    $service_log->insertGenericLog($data_log);
    drupal_set_message('Se ha guardado correctamente la empresa');
    $current_path = \Drupal::service('path.current')->getPath();

    // Clear Form.
    $form_state->setRebuild(TRUE);
    $form_state->setUserInput([]);
    $form_state->setValues([]);

    $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function _validateCompanyNumber(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // $form['enterprise_name']['#title'] = '';.
    if ($form_state->getValue('document_number')) {
      $form['enterprise_name']['#attributes']['class'][] = 'active-input';
    }
    $response->addCommand(new ReplaceCommand('#content-enterprise-name', $form['enterprise_name']));

    return $response;
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function _ajaxClosetModal(FormStateInterface $form_state) {
    $command = new CloseModalDialogCommand();
    $response = new AjaxResponse();
    $response->addCommand($command);
    return $response;
  }

  /**
   * @param $document
   * @param $number
   * @return string
   */
  public function _validateService($document, $number) {

    if (empty($number)) {
      return "";
    }

    // Validate spaces.
    $spaces = 0;
    $number = str_replace(" ", "", $number, $spaces);
    if ($spaces > 0) {
      return "Empresa no existe";
    }

    $names = $this->service_enterprise->_validateCompanyInServices($document, $number);

    if (isset($names['name_fixed'])) {
      $this->fixed = 'fixed';
    }

    if (isset($names['name_mobile'])) {
      $this->mobile = 'mobile';
    }

    if (isset($names['name_fixed'])) {
      if ($names['name_fixed']->customerInfo->lastName != NULL) {
        $name = $names['name_fixed']->customerInfo->name . ' ' . $names['name_fixed']->customerInfo->lastName;
      }
      elseif (is_array($names['name_fixed']) && isset($names['name_fixed']['name'])) {
        $name = $names['name_fixed']['name'];
      }
      else {
        $name = $names['name_fixed']->customerInfo->name;
      }
      return $name;
    }

    if (isset($names['name_mobile'])) {
      $name_mobile = $names['name_mobile']->clientName;
      return $name_mobile;
    }

    if (empty($names)) {
      return 'Empresa no existe';
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function _userAjaxForm(array &$form, FormStateInterface &$form_state) {
    $form['user_fielset']['#prefix'] = '<div id="user-form" class="user-form">';

    if ($form_state->getValue('user') == 'create') {
      $form['user_fielset']['user_form']['#prefix'] = '<div id="user-form-new" class="user-form">';
      $form['user_fielset']['user_form_old']['#prefix'] = '<div id="user-form-old" class="hidden">';
    }
    else {
      $form['user_fielset']['user_form']['#prefix'] = '<div id="user-form-new" class="hidden">';
      $form['user_fielset']['user_form_old']['#prefix'] = '<div id="user-form-old" class="user-form">';
    }

    $form['#attached']['libraries_load'][] = ['companies-list'];

    $form_state->setRebuild(TRUE);
    return $form['user_fielset'];
  }

}
