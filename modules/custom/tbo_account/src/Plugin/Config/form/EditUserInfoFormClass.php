<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\tbo_entities\Entity\CompanyUserRelations;
use Drupal\Core\Url;

/**
 * Manage config a 'EditUserDataForm' form.
 */
class EditUserInfoFormClass {

  protected $company_name;
  protected $company_id;
  protected $service_message;
  protected $repository;

  /**
   *
   */
  public function __construct() {
    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->repository = \Drupal::service('tbo_account.repository');
  }

  /**
   *
   */
  public function buildForm() {
    $twig = \Drupal::service('twig');
    $twig->addGlobal('show_create_user_form', TRUE);
    $form['#theme'] = 'form_edit_user';
    $message1 = t('Bienvenido');
    $message2 = t(', diligencie la siguiente información para completar el registro');
    $this->company_name = $_SESSION['create_account']['company_name'];
    $this->company_id = $_SESSION['create_account']['company_id'];

    $form['#prefix'] = '<div id="box-create-user" class="block-card block-form-user form-custom"><div class="box-body"><div class="row"><div class="col s12"><div class="card clearfix white"><div class="col s12"><div class="form-edit-user" data-ng-edit-user-info><div class="row mb0"><div class="col s12"><h1>¡' . $message1 . '!</h1><p class="center-align">' . $this->company_name . $message2 . '</p></div></div>';
    $form['#suffix'] = '</div></div></div></div></div></div></div>';
    $form['name'] = [
      '#type' => 'textfield',
      '#id' => 'update-user-name',
      '#title' => t('Nombres'),
      '#maxlength' => 300,
      '#attributes' => ['ng-model' => 'userName', 'ng-change' => 'validateFormUser()', 'class' => ['validate'], 'ng-init' => 'userName = \'' . $_SESSION['userInfo']['name'] . '\''],
      '#size' => 64,
    ];

    // Se obtienen los tipos de documento de la base de datos.
    $documents = \Drupal::service('tbo_entities.entities_service');
    $options_service = $documents->getAbreviatedDocumentTypes();

    $options = [];
    foreach ($options_service as $key => $data) {
      $options[$data['id']] = $data['label'];
    }

    $array = $options;

    reset($array);
    $first_key = key($array);

    $form['document_type'] = [
      '#type' => 'select',
      '#title' => t('Tipo'),
      '#options' => $options,
      '#attributes' => ['ng-model' => 'documentType', 'ng-change' => 'validateFormUser()', 'ng-init' => 'documentType = \'' . $first_key . '\''],
      '#size' => 5,
    ];
    $form['document_number'] = [
      '#type' => 'textfield',
      '#title' => t('Número de documento'),
      '#maxlength' => 40,
      '#attributes' => ['ng-model' => 'documentNumber', 'ng-change' => 'validateFormUser()', 'class' => ['validate']],
      '#size' => 64,
    ];
    $form['cel_number'] = [
      '#type' => 'textfield',
      '#title' => t('Número celular'),
      '#maxlength' => 14,
      '#attributes' => [
        'ng-model' => 'phoneNumber',
        'ng-change' => 'formatPhone()',
        'ng-init' => 'phoneNumber = \'' . substr($_SESSION['userInfo']['phone_number'], -10) . '\'',
      ],
      '#size' => 64,
    ];
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $mail = $account->getEmail();
    $form['mail'] = [
      '#type' => 'email',
      '#title' => t('Correo electrónico'),
      '#attributes' => ['disabled' => 'disabled', 'class' => ['validate', 'disabled']],
      '#value' => $mail,
      '#size' => 64,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#id' => 'submit-edit-user',
      '#value' => t('CONTINUAR'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary', 'disabled'],
      ],
    ];

    $form['#attached']['library'][] = 'tbo_account/edit-user-info';
    return $form;
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $phone = str_replace('(', '', $form_state->getValue('cel_number'));
    $phone = str_replace(')', '', $phone);
    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('-', '', $phone);
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $user = User::load($uid);

    try {
      $query_1 = \Drupal::database()->select('users_field_data', 'user');
      $query_1->addField('user', 'uid');
      $query_1->addField('user', 'name');
      $db_or = db_or();
      $db_or->condition('user.name', $form_state->getValue('name'), '=');
      $db_or->condition('user.full_name', $form_state->getValue('name'), '=');
      $query_1->condition($db_or);
      $names = $query_1->execute()->fetchAll();

      // actualización información usuario.
      if (count($names) == 0) {
        $user->set('name', $form_state->getValue('name'));
      }

      $user->set('document_type', $form_state->getValue('document_type'));
      $user->set('phone_number', $phone);
      $user->set('document_number', $form_state->getValue('document_number'));
      $user->set('mail', $form_state->getValue('mail'));
      $user->set('full_name', $form_state->getValue('name'));
      $user->addRole('admin_company');
      $user->save();

      // asignación de usuario como admin empresa de la empresa creada.
      $query = \Drupal::database()->select('company_user_relations_field_data', 'relations');
      $query->addField('relations', 'id');
      $query->condition('relations.users', $uid);
      $query->condition('relations.company_id', $this->company_id);
      $relations = $query->execute()->fetchAll();

      if (count($relations) < 1) {
        $company_user_role = CompanyUserRelations::create([
          'name' => $this->company_name,
          'users' => $uid,
          'company_id' => $this->company_id,
          'status' => TRUE,
        ]);
        $company_user_role->save();
      }

      $user_name = $form_state->getValue('name');

      // Log de auditoria.
      $service = \Drupal::service('tbo_core.audit_log_service');
      $service->loadName();

      $query = \Drupal::service('tbo_account.repository');
      $result_query = $query->getCompanyDocument($this->company_id);
      $company_number = $result_query[0]->document_number;
      $company_doc_type = $result_query[0]->document_type;

      $data = [
        'companyName' => $this->company_name,
        'companyDocument' => $company_number,
        'companySegment' => t('No disponible'),
        'event_type' => t('Cuenta'),
        'description' => t('Usuario se auto gestiona como admin empresa'),
        'details' => t('Usuario @user se auto gestiona correctamente como admin empresa con documento @tipo @numero', ['@user' => $user->getAccountName(), '@tipo' => $form_state->getValue('document_type'), '@numero' => $form_state->getValue('document_number')]),
        'old_value' => 'No disponible',
        'new_value' => 'No disponible',
      ];

      $service->insertGenericLog($data);

      $uid = \Drupal::currentUser()->id();
      $account = User::load($uid);

      // Get name rol.
      $rol = \Drupal::service('tbo_core.repository')
        ->getRoleName($account->get('roles')->getValue()[0]['target_id']);

      // Envio de correo.
      $name = '';
      $account_fields = \Drupal::currentUser()->getAccount();
      if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
        $name = $account_fields->full_name;
      }
      else {
        $name = \Drupal::currentUser()->getAccountName();
      }

      $role = $account->getRoles()['1'];

      $tokens = [
        'date' => date(),
        'user' => $name,
        'name' => $this->company_name,
        'enterprise' => $this->company_name,
        'enterprise_num' => $company_number,
        'enterprise_doc' => $company_doc_type,
        'admin_enterprise' => $name,
        'role' => $role,
        'admin_mail' => $account->getEmail(),
        'admin_phone' => $phone,
        'phone_to_send' => $phone,
        'creator_docType' => $account->get('document_type')->getValue()[0]['target_id'],
        'creator_docNumber' => $account->get('document_number')->getValue()[0]['value'],
        'link' => $GLOBALS['base_url'],
      ];

      $super_admins = $this->repository->getAllEmailUserCompany();

      if ($_SESSION['create_account']['company_is_new']) {
        foreach ($super_admins as $admin) {
          $tokens['user'] = $admin->full_name;
          $tokens['mail_to_send'] = $admin->mail;
          $send = $this->service_message->send_message($tokens, 'assing_enterprise_super_admin');
        }
        $tokens['user'] = $name;
        $tokens['mail_to_send'] = $account->getEmail();

        // Sms.
        $tokens['role'] = $role;

        $tokens['username'] = $name;

        // It is commented to solve the jira tbo2-297 which indicates that double mail should not be reached in the autocreacion process of account and the second mail arrived with the form of the edit, same for sending the message
        // $send = $this->service_message->send_message($tokens, 'assing_enterprise_super_admin');
        // $sendSms = $this->service_message->send_sms('autocreate', $tokens);.
      }
      else {
        foreach ($super_admins as $admin) {
          $tokens['mail_to_send'] = $admin->mail;
          $send = $this->service_message->send_message($tokens, 'assing_enterprise_old');
        }
        $tokens['mail_to_send'] = $account->getEmail();
        $send = $this->service_message->send_message($tokens, 'assing_enterprise_old');
      }

      // Unset user tifo id info.
      unset($_SESSION['userInfo']);
      // redirección.
      $twig = \Drupal::service('twig');
      $twig->addGlobal('show_create_user', FALSE);
      drupal_set_message(t('Bienvenido @user. La cuenta de su empresa @company se ha creado con éxito.', ['@user' => $user->getAccountName(), '@company' => $this->company_name]));
    }
    catch (Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
    }
    $url = Url::fromUri('internal:/tbo_general/selector/0');
    // Save segment track to first login.
    $_SESSION['first_login'] = TRUE;

    // Save segment track.
    $event = 'TBO - Autocrear empresa - Tx';
    $category = 'Crear cuenta';
    $environment = $_SESSION['create_account']['environment_segment'];
    $label = 'Continuar - Exitoso - ' . $environment;
    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);

    $form_state->setRedirectUrl($url);
  }

}
