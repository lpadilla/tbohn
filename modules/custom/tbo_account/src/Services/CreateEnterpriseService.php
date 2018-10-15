<?php

namespace Drupal\tbo_account\Services;

use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_entities\Entity\CompanyEntity;
use Drupal\user\Entity\User;
use Drupal\tbo_entities\Entity\CompanyUserRelations;

/**
 * Class CreateAccountService.
 *
 * @package Drupal\tbo_account
 */
class CreateEnterpriseService {

  protected $tbo_config;

  protected $api;

  protected $company_document;

  protected $config_count;

  protected $fixed = 'no fixed';

  protected $mobile = 'no mobile';

  protected $name_company;

  protected $name_user;

  /**
   * Constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   TBO API Client interface.
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * Validate enterprise services.
   *
   * @param string $document
   *   Document type.
   * @param string $number
   *   Document number.
   *
   * @return array
   *   Customer accounts.
   */
  public function _validateCompanyInServices($document, $number) {
    $useAlternative = FALSE;
    $config = \Drupal::config('tbo_account.autocreateformconfig');
    $option = $config->get('option');
    if ($option == 'alternative') {
      $useAlternative = TRUE;
    }
    $names = [];
    // Get service fixed.
    $params = [
      'tokens' => [
        'docType' => $document,
        'clientId' => $number,
      ],
      // Next validate movil.
      'no_exception' => TRUE,
    ];

    $name_fixed = $this->api->customerByCustomerId($params);
    if (!$name_fixed && $useAlternative) {
      $name = $this->findCustomerAccountsByIdentification($number, strtolower($document));
      if ($name != "") {
        $names['name_fixed'] = ['name' => $name];
      }
    }

    if ($name_fixed) {
      $names['name_fixed'] = $name_fixed;
    }

    // Get service mobile.
    $day = date('d');
    $month = $lastMonth = date('m');
    $year = $lastYear = date('Y');
    if ($month != 1) {
      $lastMonth = $lastMonth - 1;
    }
    else {
      $lastMonth = 12;
      $lastYear = $lastYear - 1;
    }

    $params_m = [
      'query' => [
        'clientType' => strtoupper((string) $document),
        'countInvoiceToReturn' => 1,
        'startDate' => "$day%2F$month%2F$year",
        'endDate' => "$day%2F$month%2F$year",
        'type' => 'mobile',
      ],
      'tokens' => [
        'clientId' => $number,
      ],
      // Next with validate.
      'no_exception' => TRUE,
    ];

    $name_mobile = $this->api->getBillingInformation($params_m);

    if ($name_mobile) {
      $names['name_mobile'] = $name_mobile;
    }
    else {
      $params_m['query']['startDate'] = "$day%2F$lastMonth%2F$lastYear";

      $name_mobile = $this->api->getBillingInformation($params_m);
      if ($name_mobile) {
        $names['name_mobile'] = $name_mobile;
      }
    }

    return $names;
  }

  /**
   * Creates a company.
   *
   * @param array $company_data
   *   New company data.
   */
  public function _createCompany($company_data) {
    // Get check digit configuration.
    $config_dv = \Drupal::config('tbo_general.settings')->get('region')['dv'];

    // Validate configuration and set dv if config is true.
    if (!empty($config_dv) && $config_dv == 1) {
      $dv = \Drupal::service('tbo_entities_co.miscellany')
        ->getDv($company_data['document_number']);
      $company_data['cod_uni'] = $dv;
    }

    $company = CompanyEntity::create($company_data);

    $company->save();

    // Now we create the cards access permissions for this new company.
    $database = \Drupal::database();
    $tableExist = $database->schema()
      ->tableExists('cards_access_by_company_permissions');
    if ($tableExist) {
      $newCompanyId = $company->id();
      if ($newCompanyId) {
        $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');
        $permissionsRepository->createCompanyPermissionsSet($newCompanyId);
      }
    }
  }

  /**
   * Implements create user.
   *
   * @param array $data
   *   The user data.
   * @param string $functionality
   *   The functionality save user.
   */
  public function _createUser($data = [], $functionality = '') {
    // Create user.
    $user = User::create();
    $user->setPassword('0000');
    $user->enforceIsNew();
    $user->setEmail($data['mail']);
    $user->setUsername($data['username']);
    $user->set('phone_number', $data['phone_number']);
    $user->set('document_number', $data['document_number']);
    $user->set('document_type', $data['document_type']);
    $user->set('full_name', $data['full_name']);
    $user->addRole('admin_company');
    $user->save();

    // Save LogCreateUser.
    $service = \Drupal::service('tbo_account.create_companies_service');
    $service->insertLogCreateUser($functionality, $data['full_name'], $data['document_type'], $data['phone_number']);
  }

  /**
   * Create a company - user relation.
   *
   * @param array $data_relation
   *   New relation data.
   */
  public function _CreateCompanyUserRelation($data_relation) {
    // Create relations company-user.
    $company_user_role = CompanyUserRelations::create([
      'name' => $data_relation['name'],
      'users' => $data_relation['users'],
      'company_id' => $data_relation['company_id'],
      'associated_id' => $data_relation['associated_id'],
      'status' => $data_relation['status'],
    ]);
    $company_user_role->save();
  }

  /**
   * Send SMS message.
   *
   * @param string $phone_number
   *   Phone number.
   * @param string $sms_message
   *   Text message.
   * @param string $accion
   *   Audit log parameter.
   * @param string $user_name
   *   Audit log parameter.
   * @param string $company
   *   Audit log parameter.
   */
  public function _sendSms($phone_number, $sms_message, $accion = '', $user_name = '', $company = '') {

    $params['query'] = [
      'from' => '85573',
      'to' => $phone_number,
      'text' => $sms_message,
    ];

    try {
      $sendSms = $this->api->sendSMS($params);
    }
    catch (\Exception $e) {
      \Drupal::logger('sendSms')
        ->error('Phone ' . $phone_number . ' Error: ' . $e->getMessage());
      $service = \Drupal::service('tbo_core.audit_log_service');
      $service->loadName();
      // Create array data[].
      $data = [
        'event_type' => t('Cuenta'),
        'description' => t('Error en el envio del SMS'),
        'details' => 'Usuario ' . $service->getName() . ' presento error en ' . $accion . ' al enviar el mensaje al admin empresa ' . $user_name . ' de la empresa ' . $company . ', con numero de telefono ' . $phone_number,
      ];

      // Save audit log.
      $service->insertGenericLog($data);
    }
  }

  /**
   * Get the customer's accounts using his identification data.
   *
   * @param string $clientId
   *   Document number.
   * @param string $documentType
   *   Document type.
   *
   * @return mixed
   *   Search results.
   */
  private function findCustomerAccountsByIdentification($clientId, $documentType) {
    $result = FALSE;
    $params['tokens'] = [
      'docType' => $documentType,
      'clientId' => $clientId,
    ];
    try {
      $response = $this->api->findCustomerAccountsByIdentification($params);

    }
    catch (\Exception $e) {
      return $result;
    }
    if (isset($response->organization->name)) {
      $result = $response->organization->name;
    }
    return $result;

  }

  /**
   * Implements insertLogCreateUser().
   *
   * @param string $functionality
   *   The functionality save of user.
   * @param string $user
   *   The user save.
   * @param string $type
   *   The document type.
   * @param string $number
   *   The document number.
   */
  public function insertLogCreateUser($functionality = '', $user = '', $type = '', $number = '') {
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data[].
    $data = [
      'event_type' => 'Registro crear usuario',
      'description' => t('Registro de creación de usuario por funcionalidad'),
      'details' => t('Usuario @user_name creo el usuario @user con el tipo de documento @type y numero de documento @number por la funcionalidad de @functionality', [
        '@user_name' => $service->getName(),
        '@user' => $user,
        '@type' => $type,
        '@number' => $number,
        '@functionality' => $functionality,
      ]),
    ];

    // Save audit log.
    $service->insertGenericLog($data);
  }

}
