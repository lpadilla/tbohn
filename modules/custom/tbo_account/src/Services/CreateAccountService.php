<?php

namespace Drupal\tbo_account\Services;

use Drupal\user\Entity\User;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\tbo_entities\Entity\CompanyEntity;
use Drupal\tbo_general\Services\TboConfigServiceInterface;

/**
 * Class CreateAccountService.
 *
 * @package Drupal\tbo_account
 */
class CreateAccountService {
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
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   *   TBO Configuration.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   TBO API client.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $account_fields = \Drupal::currentUser()->getAccount();
    $config_count = \Drupal::config('tbo_account.autocreateaccountformconfig')->get('limit_failed_attempts')['number'];

    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->config_count = is_null($config_count) ? 3 : $config_count;

    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $this->name_user = $account_fields->full_name;
    }
    else {
      $this->name_user = \Drupal::currentUser()->getAccountName();
    }
  }

  /**
   * Implement of setFixed.
   *
   * @param mixed $value
   *   Value.
   */
  public function setFixed($value = '') {
    $this->fixed = $value;
  }

  /**
   * Implement of setMobile.
   *
   * @param mixed $value
   *   Value.
   */
  public function setMobile($value = '') {
    $this->mobile = $value;
  }

  /**
   * Implement of setCompanyName.
   *
   * @param mixed $companyName
   *   Company name.
   */
  public function setCompanyName($companyName = '') {
    $this->name_company = $companyName;
  }

  /**
   * Implement of getCreateAccountForm.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function getCreateAccountForm() {
    $twig = \Drupal::service('twig');
    $twig->addGlobal('show_create_account', TRUE);
    $form = \Drupal::formBuilder()
      ->getForm('Drupal\tbo_account\Form\CreateAccountForm');
    return $form;
  }

  /**
   * Implement of getCustomerByContractId.
   *
   * @param mixed $contractId
   *   Contract  id.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function getCustomerByContractId($contractId) {
    try {
      $params['tokens'] = [
        'contractId' => $contractId,
      ];

      $response = $this->api->getCustomerByContractId($params);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $response;
  }

  /**
   * Find customer bills history by account id.
   *
   * @param string $contractId
   *   Contract ID.
   *
   * @return mixed
   *   Service response.
   */
  public function findCustomerBillsHistoryByAccountId($contractId) {
    try {
      $params['tokens'] = [
        'contractId' => $contractId,
      ];

      $response = $this->api->findCustomerBillsHistoryByAccountId($params);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $response;
  }

  /**
   * Implement of validateForm.
   *
   * @param mixed $documentType
   *   Document type.
   * @param mixed $clientId
   *   Client id.
   * @param mixed $contractId
   *   Contract  id.
   * @param mixed $referentPayment
   *   Referent payment id.
   * @param mixed $form_id
   *   Form id.
   * @param mixed $type
   *   Type.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function validateForm($documentType, $clientId, $contractId, $referentPayment, $form_id = '', $type = 'fixed') {
    $message = "Inicio de autocreacion con tipo de documento @document_type, numero de documento @document_numer, contrato @contract, referencia de pago @payment_reference y tipo @environment";
    $binds = [
      '@document_type' => $documentType,
      '@document_numer' => $clientId,
      '@contract' => $contractId,
      '@payment_reference' => $referentPayment,
      '@environment' => $type,
    ];

    \Drupal::logger('Autocreate')->alert($message, $binds);

    switch ($type) {
      case 'fixed':
        $response = $this->validateFormServiceFixed($documentType, $clientId, $contractId, $referentPayment, $form_id);
        break;

      case 'mobile':
        $response = $this->validateFormServiceMobile($documentType, $clientId, $contractId, $referentPayment, $form_id);
        break;
    }

    return $response;
  }

  /**
   * Implement of validateFormServiceFixed.
   *
   * @param mixed $documentType
   *   Document type.
   * @param mixed $clientId
   *   Client id.
   * @param mixed $contractId
   *   Contract  id.
   * @param mixed $referentPayment
   *   Referent payment id.
   * @param mixed $form_id
   *   Form id.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function validateFormServiceFixed($documentType, $clientId, $contractId, $referentPayment, $form_id = '') {
    $data = [];
    $contracts_references = [];
    $documentType = strtolower($documentType);

    // Get service fixed.
    $response_fixed = $this->findCustomerAccountsByIdentification($clientId, $documentType, $contractId, $referentPayment);

    if ($response_fixed) {
      $this->fixed = 'fixed';
      $this->name_company = $response->organization->name;
      $contractsCollection = $response_fixed->contractsCollection;

      if (is_object($contractsCollection)) {
        array_push($data, $contractsCollection);
      }
      else {
        foreach ($contractsCollection as $item) {
          array_push($data, $item);
        }
      }

      foreach ($data as $detail) {
        $contracts_references[$detail->contractId] = $detail->contractId;
      }

      if (!array_key_exists($contractId, $contracts_references)) {
        $message_error['referent_payment'] = t('El referente de pago no es válido.');
      }
    }
    else {
      $message_error['document_number'] = t('El tipo o número de documento no son válidos.');
    }

    return $message_error;
  }

  /**
   * Implement of validateFormServiceMobile.
   *
   * @param mixed $documentType
   *   Document type.
   * @param mixed $clientId
   *   Client id.
   * @param mixed $contractId
   *   Contract  id.
   * @param mixed $referentPayment
   *   Referent payment id.
   * @param mixed $form_id
   *   Form id.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function validateFormServiceMobile($documentType, $clientId, $contractId, $referentPayment, $form_id) {
    $data = [];
    $contracts_references = [];

    // Get service mobile.
    $response_fixed = $this->getAccountByDocIdAndDocType($documentType, $clientId, $contractId, $referentPayment);

    if ($response_fixed) {
      $this->mobile = 'mobile';
      $this->name_company = $response_fixed->payload->organization->name;
      $customerAccountCollection = $response_fixed->payload->customerAccountCollection;

      if (is_object($customerAccountCollection)) {
        array_push($data, $customerAccountCollection);
      }
      else {
        foreach ($customerAccountCollection as $item) {
          array_push($data, $item);
        }
      }

      foreach ($data as $detail) {
        $contracts_references[$detail->accountNumber] = $detail->accountNumber;
      }

      if (!array_key_exists($referentPayment, $contracts_references)) {
        $message_error['contract_number'] = t('El número de contrato no es válido.');
      }
    }
    else {
      $message_error['document_number'] = t('El tipo o número de documento no son válidos.');
    }

    // Validamos la cantidad de intentos fallidos.
    if ($message_error) {
      $this->checkLimitSubmit($form_id, TRUE);
    }

    return $message_error;
  }

  /**
   * Implement of getAccountByDocIdAndDocType.
   *
   * @param mixed $documentType
   *   Document type.
   * @param mixed $clientId
   *   Client id.
   * @param mixed $contractId
   *   Contract  id.
   * @param mixed $referentPayment
   *   Referent payment id.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function getAccountByDocIdAndDocType($documentType, $clientId, $contractId = '', $referentPayment = '') {
    try {
      $day = date('d');
      $month = date('m');
      $year = date('Y');
      $pastYear = $year - 1;
      $nextYear = $year + 1;

      $params['query'] = [
        'docId' => $clientId,
        'docType' => $documentType,
      ];

      return $this->api->getAccountByDocIdAndDocType($params);
    }
    catch (\Exception $error) {
      $messageError = json_decode($error->getMessage());
      $codeError = isset($messageError->status) ? $messageError->status : 400;
      $messageError = isset($messageError->response->error) ? $messageError->response->error : 'bad request';

      $this->saveAuditLog(
        'Se presenta error en el WS al intentar auto crear la cuenta.',
        'Usuario ' . $this->name_user . ' accede a auto creación de cuenta y se genera el error de validación con los siguientes datos: Tipo Documento ' . $documentType . ' Número de Documento ' . $clientId . ' Número Referencia ' . $referentPayment . ' Contrato ' . $contractId,
        'Servicio web "getAccountByDocIdAndDocType" código "' . $codeError . '" descripción error "' . $messageError . '".'
      );

      return FALSE;
    }
  }

  /**
   * Implement of findCustomerAccountsByIdentification.
   *
   * @param mixed $clientId
   *   Client id.
   * @param mixed $documentType
   *   Document type.
   * @param mixed $contractId
   *   Contract  id.
   * @param mixed $referentPayment
   *   Referent payment id.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  private function findCustomerAccountsByIdentification($clientId, $documentType, $contractId = '', $referentPayment = '') {
    $params['tokens'] = [
      'docType' => $documentType,
      'clientId' => $clientId,
    ];

    try {
      return $this->api->findCustomerAccountsByIdentification($params);
    }
    catch (\Exception $error) {
      $messageError = json_decode($error->getMessage());
      $codeError = isset($messageError->error->statusCode) ? $messageError->error->statusCode : 400;
      $messageError = isset($messageError->error->message) ? $messageError->error->message : 'bad request';

      $this->saveAuditLog(
        'Se presenta error en el WS al intentar auto crear la cuenta.',
        'Usuario ' . $this->name_user . ' accede a auto creación de cuenta y se genera el error de validación con los siguientes datos: Tipo Documento ' . $documentType . ' Número de Documento ' . $clientId . ' Número Referencia ' . $referentPayment . ' Contrato ' . $contractId,
        'Servicio web "findCustomerAccountsByIdentification" código "' . $codeError . '" descripción error "' . $messageError . '".'
      );

      return FALSE;
    }
  }

  /**
   * Implement of validateServiceExistCompany.
   *
   * @param mixed $clientId
   *   Client id.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function validateServiceExistCompany($clientId) {
    // Consultamos la tabla company_entity_field_data en la base de datos
    // comparando el valor de la columna document_number, con $clientId
    // si no retorna un registro es por que la empresa ya esta creada.
    $database = \Drupal::database();
    $query = $database->select('company_entity_field_data', 'company_data');
    $query->addField('company_data', 'id');
    $query->addField('company_data', 'name');
    $query->condition('company_data.document_number', $clientId);
    $result = $query->execute()->fetchAll();

    if ($result) {
      return reset($result);
    }

    return FALSE;
  }

  /**
   * Implement of checkLimitSubmit.
   *
   * @param mixed $form_id
   *   Form id.
   * @param mixed $create_row
   *   Create row.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function checkLimitSubmit($form_id, $create_row = FALSE) {
    // Validamos la cantidad de intentos fallidos.
    $service_limit = \Drupal::service('limit_submissions_service');
    $email = \Drupal::currentUser()->getEmail();

    if ($rows_limit = $service_limit->getRows($email, 'email', $form_id)) {
      // Es decir todavia lo puede volver a intentar.
      if ((count($rows_limit) + 1) <= $this->config_count) {
        $status = TRUE;
      }
      else {
        // Log de auditoria.
        $this->saveAuditLog('Usuario supera límite de intentos para auto crear cuenta.', 'supera límite de ' . $this->config_count . ' intentos disponibles para auto crear cuenta.');
        $status = FALSE;
      }
    }
    // Es decir todavia lo puede volver a intentar.
    else {
      $status = TRUE;
    }

    if ($status && $create_row) {
      $service_limit->createRow($email, 'email', $form_id);
    }

    return $status;
  }

  /**
   * Implement of createCompanyEntity.
   *
   * @param mixed $documentType
   *   Document type.
   * @param mixed $clientId
   *   Client id.
   * @param mixed $segment
   *   Segment.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function createCompanyEntity($documentType, $clientId, $segment) {
    $user = \Drupal::currentUser();
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

      // Get check digit configuration.
      $config_dv = \Drupal::config('tbo_general.settings')->get('region')['dv'];
      $code_uni = '';

      // Validate configuration and set dv if config is true.
      if (!empty($config_dv) && $config_dv == 1) {
        $dv = \Drupal::service('tbo_entities_co.miscellany')->getDv($clientId);
        $code_uni = $dv;
      }

      // Crear empresa.
      $company = CompanyEntity::create([
        'name' => $this->name_company,
        'document_type' => strtolower($documentType),
        'document_number' => $clientId,
        'company_name' => $this->name_company,
        'segment' => $segment,
        'status' => TRUE,
        'fixed' => $fixed,
        'mobile' => $mobile,
        'cod_uni' => $code_uni,
      ]);

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

      $detail = 'auto crea correctamente la empresa con documento ' . $documentType . ' ' . $clientId . '.';
      if ($this->fixed) {
        $config = \Drupal::config('tbo_account.autocreateformconfig');
        $method = $config->get('method');
        $detail .= ' La empresa se creó por el método ' . $method . '.';
      }

      // Log de auditoria.
      $this->saveAuditLog('Usuario auto crea correctamente una empresa.', $detail);

      // Envio de correo.
      $service_message = \Drupal::service('tbo_mail.send');
      $tokens = [
        'date' => time(),
        'user' => '',
        'name' => '',
        'enterprise' => $this->name_company,
        'enterprise_num' => '',
        'document' => '',
        'creator' => '',
        'creator_mail' => '',
        'creator_phone' => '',
        'creator_docType' => $documentType,
        'creator_docNumber' => $clientId,
        'mail_to_send' => $user->getEmail(),
      ];
      $send = $service_message->send_message($tokens, 'assing_enterprise_super_admin');

    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $company;
  }

  /**
   * Implement of saveAuditLog.
   *
   * @param mixed $description
   *   Description.
   * @param mixed $details
   *   Details.
   * @param mixed $technicalDetail
   *   Technical detail.
   */
  public function saveAuditLog($description, $details, $technicalDetail = '') {
    $log = AuditLogEntity::create();
    // Temporary.
    $segment = 'No disponible';
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    // Get name rol.
    $rol = \Drupal::service('tbo_core.repository')
      ->getRoleName($account->get('roles')->getValue()[0]['target_id']);

    $log->set('user_names', $this->name_user);
    $log->set('created', time());
    $log->set('company_segment', $segment);
    $log->set('user_id', $uid);
    $log->set('user_names', $this->name_user);
    $log->set('company_name', isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : t('No aplica'));
    $log->set('company_document_number', isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : t('No aplica'));
    $log->set('user_role', isset($rol) ? $rol : t('Sin rol'));
    $log->set('event_type', t('Cuenta'));
    $log->set('description', $description);
    $log->set('details', 'Usuario ' . $this->name_user . ' ' . $details);
    $log->set('old_values', 'No disponible');
    $log->set('new_values', 'No disponible');

    if (!empty($technicalDetail)) {
      $log->set('technical_detail', $technicalDetail);
    }

    $log->save();
  }

}
