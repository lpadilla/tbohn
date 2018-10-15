<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\tbo_entities\Entity\CompanyUserRelations;

/**
 * Manage config a 'AutoCreateAccountFormClass' block.
 */
class AutoCreateAccountFormClass {
  protected $service_message;
  protected $create_account;
  protected $config_count;
  protected $config_help_card;
  protected $repository;
  private $segment;

  /**
   * Implement of __construct.
   */
  public function __construct() {
    $config_count = \Drupal::config('tbo_account.autocreateaccountformconfig')->get('limit_failed_attempts')['number'];

    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->create_account = \Drupal::service('tbo_account.create_account');
    $this->config_count = is_null($config_count) ? 3 : $config_count;
    $this->config_help_card = \Drupal::config('tbo_general.settings.help_card')->getRawData();
    $this->repository = \Drupal::service('tbo_account.repository');

    // Segment.
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
  }

  /**
   * Implement of getFormId.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function getFormId() {
    return 'create_account';
  }

  /**
   * Implement of buildForm.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function buildForm(array &$form, FormStateInterface $form_state) {
    $typeDocuments = [];
    $current_user = \Drupal::currentUser();
    $form['#theme'] = 'form_create_account';
    $defaultValues = $form_state->getUserInput();
    $config = \Drupal::config('tbo_account.autocreateaccountformconfig')->get('form_config');

    if ($defaultValues) {
      $defaultDocumentType = $defaultValues['document_type'];
      $defaultDocumentNumber = $defaultValues['document_number'];
      $defaultServiceType = $defaultValues['service_type'];
      $defaultReferentPayment = $defaultValues['referent_payment'];
      $defaultContractNumber = $defaultValues['contract_number'];
    }

    // Log de auditoria.
    $this->create_account->saveAuditLog('Usuario accede a auto creación de cuenta.', 'accede a auto creación de cuenta.');
    $form['#tree'] = TRUE;

    // Return.
    $query = \Drupal::database()->select('company_user_relations_field_data', 'relations');
    $query->addField('relations', 'id');
    $query->condition('relations.users', $current_user->id());

    if (count($query->execute()->fetchAll()) <= 1) {
      $form['url_return'] = Url::fromUri('internal:/inicio');
    }
    else {
      $form['url_return'] = Url::fromUri('internal:/tbo_general/selector/0');
    }

    // Title.
    $field = 'title';
    $titleLabel = $config[$field]['label'];

    if ($titleLabel == NULL) {
      $titleLabel = 'Cree su cuenta';
    }

    // Text help.
    $field = 'help';
    $helpLabel = $config[$field]['label'];
    $helpText = $config[$field]['text'];

    if ($helpLabel == NULL) {
      $helpLabel = 'Recuerde';
    }
    if ($helpText == NULL) {
      $helpText = 'Deberá tener a la mano una factura de servicios de los últimos 3 meses con el número de contrato y referencia de pago.';
    }
    $form['title'] = t($titleLabel);
    $form['help'] = '<strong>' . t($helpLabel) . ':</strong><br>' . $helpText;

    // Se obtienen los tipos de documento de la base de datos.
    $optionsService = \Drupal::service('tbo_entities.entities_service')->getAbreviatedDocumentTypes();

    foreach ($optionsService as $key => $data) {
      $typeDocuments[$data['id']] = $data['label'];
    }

    // Document.
    $field = 'document';
    $documentPlaceholder = $config[$field]['placeholder'];
    $documentPlaceholderNit = $config[$field]['placeholderNit'];
    $documentDescription = $config[$field]['description'];

    if ($documentPlaceholder == NULL) {
      $documentPlaceholder = 'Ingrese el número de documento.';
    }
    if ($documentPlaceholderNit == NULL) {
      $documentPlaceholderNit = 'Ingrese el número de documento sin el digito de verificación.';
    }
    if ($documentDescription == NULL) {
      $documentDescription = 'Ingresar sin signos de puntuación.';
    }

    $label = 'label_doctype = "' . $documentPlaceholder . '"; ' . 'label_doctype_nit = "' . $documentPlaceholderNit . '"; ';

    $form['document_type'] = [
      '#type' => 'select',
      '#options' => $typeDocuments,
      '#default_value' => isset($defaultDocumentType) ? $defaultDocumentType : array_keys($typeDocuments)[0],
      '#attributes' => [
        'ng-model' => 'document_type',
        'ng-change' => 'validateTypeNumber()',
        'ng-init' => isset($defaultDocumentType) ? $label . 'document_type = ' . $defaultDocumentType : $label ,
      ],
    ];
    $form['document_number'] = [
      '#type' => 'textfield',
      '#title' => t($documentPlaceholder),
      '#suffix' => '<p>' . t($documentDescription) . '</p>',
      '#attributes' => [
        'ng-model' => 'document_number',
        'ng-change' => 'validateForm()',
        'ng-trim' => 'false',
        'ng-init' => isset($defaultDocumentNumber) ? 'document_number = ' . $defaultDocumentNumber : '',
        'class' => ['validate'],
        'disallow-spaces' => TRUE,
      ],
      '#error_no_message' => TRUE,
    ];

    // Type of service.
    $field = 'service';
    $serviceHelp = $config[$field]['help'];
    $serviceMobilePlaceholder = $config[$field]['mobile']['placeholder'];
    $serviceMobileDescription = $config[$field]['mobile']['description'];
    $serviceMobileLink = $config[$field]['mobile']['link'];
    $serviceMobileNode = $config[$field]['mobile']['node'];
    $serviceMobileModal = $config[$field]['mobile']['modal'];
    $serviceMobileTarget = $config[$field]['mobile']['target'];
    $serviceFixedPlaceholder = $config[$field]['fixed']['placeholder'];
    $serviceFixedDescription = $config[$field]['fixed']['description'];
    $serviceFixedLink = $config[$field]['fixed']['link'];
    $serviceFixedNode = $config[$field]['fixed']['node'];
    $serviceFixedModal = $config[$field]['fixed']['modal'];
    $serviceFixedTarget = $config[$field]['fixed']['target'];

    if ($serviceHelp == NULL) {
      $serviceHelp = 'Seleccione el tipo de servicio con el que desea registrarse.';
    }
    if ($serviceMobilePlaceholder == NULL) {
      $serviceMobilePlaceholder = 'Ingrese el referente de pago.';
    }
    if ($serviceMobileDescription == NULL) {
      $serviceMobileDescription = '¿Dónde encontrar el referente de pago?';
    }
    if ($serviceMobileModal == NULL) {
      $serviceMobileModal = 0;
    }
    if ($serviceMobileTarget == NULL) {
      $serviceMobileTarget = '_blank';
    }
    if ($serviceFixedPlaceholder == NULL) {
      $serviceFixedPlaceholder = 'Ingrese número de factura.';
    }
    if ($serviceFixedDescription == NULL) {
      $serviceFixedDescription = '¿Dónde encontrar el número de factura?';
    }
    if ($serviceFixedModal == NULL) {
      $serviceFixedModal = 0;
    }
    if ($serviceFixedTarget == NULL) {
      $serviceFixedTarget = '_blank';
    }

    $renderMobile = [];
    $hrefMobile = $serviceMobileModal ? 'href="#modalMobile"' : 'href="' . $serviceMobileLink . '"';
    $targetMobile = $serviceMobileModal ? '' : 'target="' . $serviceMobileTarget . '"';
    $segmentMobile = 'data-segment-event="TBO – Autocrear empresa Ayuda - Consulta" data-segment-properties=\'{ "category":"Crear cuenta", "label":"Donde puedo encontrar el referente de pago - movil"}\'';

    if ($serviceMobileModal) {
      $node = Node::load($serviceMobileNode);

      if (isset($node)) {
        $renderMobile = \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node);
      }
    }

    $renderFixed = [];
    $hrefFixed = $serviceFixedModal ? 'href="#modalFixed"' : 'href="' . $serviceFixedLink . '"';
    $targetFixed = $serviceFixedModal ? '' : 'target="' . $serviceFixedTarget . '"';
    $segmentFixed = 'data-segment-event="TBO – Autocrear empresa Ayuda - Consulta" data-segment-properties=\'{ "category":"Crear cuenta", "label":"Donde puedo encontrar el número de contrato - fijo"}\'';

    if ($serviceFixedModal) {
      $node = Node::load($serviceFixedNode);

      if (isset($node)) {
        $renderFixed = \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node);
      }
    }

    $form['service_help'] = $serviceHelp;
    $form['service_type'] = [
      '#type' => 'select',
      '#options' => [
        'mobile' => t('Servicios móviles'),
        'fixed' => t('Servicios fijos'),
      ],
      '#default_value' => isset($defaultServiceType) ? $defaultServiceType : 'mobile' ,
      '#attributes' => [
        'ng-model' => 'service_type',
        'ng-change' => 'validateForm()',
        'ng-init' => isset($defaultServiceType) ? 'service_type = ' . $defaultServiceType : '',
      ],
    ];
    $form['referent_payment'] = [
      '#type' => 'textfield',
      '#title' => t($serviceMobilePlaceholder),
      '#suffix' => '<a class="waves-effect waves-light segment-click" ' . $hrefMobile . ' ' . $targetMobile . ' ' . $segmentMobile . '>' . t($serviceMobileDescription) . '</a>',
      '#attributes' => [
        'ng-model' => 'referent_payment',
        'ng-change' => 'validateForm()',
        'ng-trim' => 'false',
        'ng-init' => isset($defaultReferentPayment) ? 'referent_payment = "' . $defaultReferentPayment . '"' : '',
        'class' => ['validate'],
        'disallow-spaces' => TRUE,
      ],
    ];
    $form['modalMobile']['title'] = t($serviceMobileDescription);
    $form['modalMobile']['content'] = $renderMobile;
    $form['contract_number'] = [
      '#type' => 'textfield',
      '#title' => t($serviceFixedPlaceholder),
      '#suffix' => '<a class="waves-effect waves-light segment-click" ' . $hrefFixed . ' ' . $targetFixed . ' ' . $segmentFixed . '>' . t($serviceFixedDescription) . '</a>',
      '#attributes' => [
        'ng-model' => 'contract_number',
        'ng-change' => 'validateForm()',
        'ng-trim' => 'false',
        'ng-init' => isset($defaultContractNumber) ? 'contract_number = "' . $defaultContractNumber . '"' : '',
        'class' => ['validate'],
        'disallow-spaces' => TRUE,
      ],
    ];
    $form['modalFixed']['title'] = t($serviceFixedDescription);
    $form['modalFixed']['content'] = $renderFixed;

    // Terms.
    $field = 'terms';
    $termsText = $config[$field]['text'];
    $termsTextLink = $config[$field]['textLink'];
    $termsLink = $config[$field]['link'];
    $termsNode = $config[$field]['node'];
    $termsModal = $config[$field]['modal'];
    $termsTarget = $config[$field]['target'];

    if ($termsText == NULL) {
      $termsText = 'Al presionar CONTINUAR está aceptando los';
    }
    if ($termsTextLink == NULL) {
      $termsTextLink = 'Términos y condiciones';
    }
    if ($termsModal == NULL) {
      $termsModal = 0;
    }
    if ($termsTarget == NULL) {
      $termsTarget = '_blank';
    }

    $renderTerms = [];
    $hrefTerms = $termsModal ? 'href="#modalTerms"' : 'href="' . $termsLink . '"';
    $targetTerms = $termsModal ? '' : 'target="' . $termsTarget . '"';

    if ($termsModal) {
      $node = Node::load($termsNode);

      if (isset($node)) {
        $renderTerms = \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node);
      }
    }

    $form['terms'] = t($termsText) . ' <a class="waves-effect waves-light" ' . $hrefTerms . ' ' . $targetTerms . '">' . t($termsTextLink) . '</a>';
    $form['modalTerms']['title'] = t($termsTextLink);
    $form['modalTerms']['content'] = $renderTerms;

    // Tutorial.
    $field = 'tutorial';
    $tutorialText = $config[$field]['text'];
    $tutorialLink = $config[$field]['link'];
    $tutorialNode = $config[$field]['node'];
    $tutorialModal = $config[$field]['modal'];
    $tutorialTarget = $config[$field]['target'];

    if ($tutorialText == NULL) {
      $tutorialText = 'Aprenda aquí como crear su cuenta.';
    }
    if ($tutorialModal == NULL) {
      $tutorialModal = 0;
    }
    if ($tutorialTarget == NULL) {
      $tutorialTarget = '_blank';
    }

    $renderTutorial = [];
    $hrefTutorial = $tutorialModal ? 'href="#modalTutorial"' : 'href="' . $tutorialLink . '"';
    $targetTutorial = $tutorialModal ? '' : 'target="' . $tutorialTarget . '"';
    $segmentTutorial = 'class="segment-click" data-segment-event="TBO – Autocrear empresa Ayuda - Consulta" data-segment-properties=\'{ "category":"Crear cuenta","label": "Aprende a Crear su Cuenta - fijo - movil"}\'';

    if ($tutorialModal) {
      $node = Node::load($tutorialNode);

      if (isset($node)) {
        $renderTutorial = \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node);
      }
    }

    $form['tutorial'] = ' <a ' . $hrefTutorial . ' ' . $targetTutorial . ' ' . $segmentTutorial . '>' . t($tutorialText) . '</a>';
    $form['modalTutorial']['title'] = t($tutorialText);
    $form['modalTutorial']['content'] = $renderTutorial;

    // Button.
    $field = 'button';
    $buttonLabel = $config[$field]['Label'];

    if ($buttonLabel == NULL) {
      $buttonLabel = 'Continuar';
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t($buttonLabel),
      '#attributes' => [
        'class' => ['waves-effect', 'waves-light', 'btn', 'btn-primary'],
      ],
    ];

    $form['#attached']['library'][] = 'tbo_account/create-account';
    $form['#attached']['drupalSettings']['docType'] = isset($defaultDocumentType) ? $defaultDocumentType : array_keys($typeDocuments)[0];
    $form['#attached']['drupalSettings']['serviceType'] = isset($defaultServiceType) ? $defaultServiceType : 'mobile';

    return $form;
  }

  /**
   * Implement of validateForm.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $response = [];

    if ($form_state->getSubmitHandlers()) {
      return TRUE;
    }

    $documentType = strtoupper($form_state->getValue('document_type'));
    $clientId = $form_state->getValue('document_number');
    $serviceType = $form_state->getValue('service_type');
    $referentPayment = ($serviceType == 'mobile') ? $form_state->getValue('referent_payment') : '';
    $contractId = ($serviceType == 'fixed') ? $form_state->getValue('contract_number') : '';

    // Validamos cantidad de intentos.
    if ($this->create_account->checkLimitSubmit($this->getFormId())) {
      $response = $this->create_account->validateForm($documentType, $clientId, $contractId, $referentPayment, $this->getFormId(), $serviceType);

      if (!empty($response)) {
        drupal_set_message(t('Los datos son erróneos, por favor intente nuevamente.'), 'error');
        $form_state->setErrorByName('', '');
        $this->segmentError();
      }
    }
    else {
      drupal_set_message(t('Has realizado @cantidad o más intentos fallidos. Intenta nuevamente en 24 horas', ['@cantidad' => $this->config_count]), 'error');
      $form_state->setErrorByName('', '');
      $this->segmentError();
    }

    return $response;
  }

  /**
   * Implement of submitForm.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $names = '';
    $environment = '';
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $user = User::load($uid);
    $roles = $user->getRoles();

    $segment = 'segmento';
    $documentType = strtoupper($form_state->getValue('document_type'));
    $clientId = $form_state->getValue('document_number');

    // Get environment enterprise.
    $service_enterprise = \Drupal::service('tbo_account.create_companies_service');

    try {
      $names = $service_enterprise->_validateCompanyInServices(strtolower($documentType), $clientId);

      if (isset($names['name_fixed'])) {
        $environment = 'fijo';
        $this->create_account->setFixed('fixed');

        if (is_array($names['name_fixed']) && isset($names['name_fixed']['name'])) {
          $name = $names['name_fixed']['name'];
        }
        else {
          $name = $names['name_fixed']->customerInfo->name;

          if ($names['name_fixed']->customerInfo->lastName != NULL) {
            $name .= ' ' . $names['name_fixed']->customerInfo->lastName;
          }
        }
      }

      if (isset($names['name_mobile'])) {
        $this->create_account->setMobile('mobile');

        if ($environment == 'fijo') {
          $environment .= ' - movil';
        }
        else {
          $environment = 'movil';
          $name = $names['name_mobile']->clientName;
        }
      }

      $this->create_account->setCompanyName($name);
    }
    catch (\Exception $e) {
    }

    // Validamos que la empresa no este registrada en el sitio.
    $status_create = FALSE;
    $company_id = 0;
    $company_name = '';
    $company_is_new = FALSE;
    $company_exist = $this->create_account->validateServiceExistCompany($clientId);

    // Como no existe, la creamos.
    if (!$company_exist) {
      $company = $this->create_account->createCompanyEntity($documentType, $clientId, $segment);

      // Error creando la empresa.
      if (!$company) {
        drupal_set_message('Error al crear empresa, por favor verifique los datos');
        $current_path = \Drupal::service('path.current')->getPath();
        $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
        $this->segmentError();
      }
      else {
        $company_id = $company->id();
        $company_name = $company->getCompanyName();
        $company_is_new = TRUE;
        $status_create = TRUE;
      }
    }
    else {
      $company_id = $company_exist->id;
      $company_name = $company_exist->name;
      $company_is_new = FALSE;
      $status_create = FALSE;
    }

    // Send mail notification.
    if ($status_create) {
      // Envio de correo.
      $name = '';
      $account_fields = \Drupal::currentUser()->getAccount();
      if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
        $name = $account_fields->full_name;
      }
      else {
        $name = \Drupal::currentUser()->getAccountName();
      }

      $query = \Drupal::database()
        ->select('company_entity_field_data', 'company');
      $query->addField('company', 'document_type');
      $query->addField('company', 'document_number');
      $query->condition('company.id', $company_id);
      $company_query = $query->execute()->fetchAll();
      $role = $user->getRoles()['1'];

      $tokens = [
        'date' => date(),
        'user' => $name,
        'name' => $company_name,
        'enterprise' => $company_name,
        'enterprise_num' => $company_query[0]->document_number,
        'enterprise_doc' => $company_query[0]->document_type,
        'admin_enterprise' => $name,
        'role' => $role,
        'admin_mail' => $user->getEmail(),
        'admin_phone' => isset($_SESSION['userInfo']) ? substr($_SESSION['userInfo']['phone_number'], -10) : t('No disponible'),
        'phone_to_send' => isset($_SESSION['userInfo']) ? substr($_SESSION['userInfo']['phone_number'], -10) : t('No disponible'),
        'creator_docType' => $user->get('document_type')
          ->getValue()[0]['target_id'],
        'creator_docNumber' => $user->get('document_number')
          ->getValue()[0]['value'],
        'link' => $GLOBALS['base_url'],
      ];

      $super_admins = $this->repository->getAllEmailSuperAdmin($company_id);

      foreach ($super_admins as $admin) {
        $tokens['user'] = $admin->full_name;
        $tokens['mail_to_send'] = $admin->mail;
        $send = $this->service_message->send_message($tokens, 'assing_enterprise_super_admin');
      }

      $tokens_enterprise['user'] = $name;
      $tokens_enterprise['admin'] = $name;
      $tokens_enterprise['enterprise'] = $company_name;
      $tokens_enterprise['enterprise_num'] = $company_query[0]->document_number;
      $tokens_enterprise['document'] = $company_query[0]->document_type;
      $tokens_enterprise['admin_enterprise'] = $name;
      $tokens_enterprise['admin_mail'] = $user->getEmail();
      $tokens_enterprise['admin_phone'] = isset($_SESSION['userInfo']) ? substr($_SESSION['userInfo']['phone_number'], -10) : t('No disponible');
      $tokens_enterprise['mail_to_send'] = $user->getEmail();
      $tokens_enterprise['link'] = $GLOBALS['base_url'];
      $tokens_enterprise['creator'] = '';
      $send = $this->service_message->send_message($tokens_enterprise, 'autocreate_account');
    }

    // Asignación de usuario como admin empresa de la empresa creada.
    $existRelation = FALSE;
    $companySelector = \Drupal::service('tbo_general.company_selector_controller');
    $companies = $companySelector->getCompaniesUser();

    foreach ($companies as $company) {
      if ($company->company_id == $company_id) {
        $existRelation = TRUE;
      }
    }

    if (!$existRelation) {
      $company_user_role = CompanyUserRelations::create([
        'name' => $company_name,
        'users' => $uid,
        'company_id' => $company_id,
        'status' => TRUE,
      ]);

      $company_user_role->save();

      drupal_set_message(t('Bienvenido @user. La cuenta de su empresa @company se ha creado con éxito.', [
        '@user' => $user->getAccountName(),
        '@company' => $this->company_name,
      ]));
    }
    else {
      drupal_set_message(t('Bienvenido @user. La cuenta de su empresa @company se creó con anterioridad.', [
        '@user' => $user->getAccountName(),
        '@company' => $this->company_name,
      ]));
    }

    // Asignamos Rol admin empresa.
    $user->addRole('admin_company');
    $user->set('document_type', isset($user->get('document_type')->value) ? $user->get('document_type')->value : strtolower($documentType));
    $user->set('document_number', isset($user->get('document_type')->value) ? $user->get('document_type')->value : $clientId);
    $user->save();

    // Verificamos cantidad de empresas que tiene relaconadas.
    $url = '';
    $companies = $companySelector->getCompaniesUser();

    if (count($companies) == 1) {
      $url = Url::fromUri('internal:/tbo_general/selector/' . $company_id);
    }
    else {
      $url = Url::fromUri('internal:/tbo_general/selector/0');
    }

    // Save segment track.
    $event = 'TBO - Autocrear empresa - Tx';
    $category = 'Crear cuenta';
    $label = 'Continuar - Exitoso - ' . $environment;
    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);

    $form_state->setRedirectUrl($url);
  }

  /**
   * Implement of segmentError.
   */
  public function segmentError() {
    $account = \Drupal::currentUser();
    $this->segment->track(
      [
        'event' => 'TBO - Autocrear empresa - Tx',
        'userId' => $account->id(),
        'properties' => [
          'category' => 'Crear cuenta',
          'label' => 'Continuar - Fallido',
          'site' => 'NEW',
        ],
      ]
    );
  }

}
