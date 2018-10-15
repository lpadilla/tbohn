<?php

namespace Drupal\tbo_account_bo\Services;

use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\tbo_entities_bo\Entity\CompanyEntityBo;
use Drupal\tbo_entities\Entity\CompanyEntity;
use Drupal\user\Entity\User;
use Drupal\tbo_entities\Entity\CompanyUserRelations;
use Drupal\tbo_account\Services\CreateEnterpriseService;

/**
 * Class CreateAccountService.
 *
 * @package Drupal\tbo_account
 */
class CreateCompanyServiceBo extends CreateEnterpriseService{


  /**
   * Constructor.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * @param $document
   * @param $number
   * @return string
   */
  public function _validateCompanyInServices($document, $number) {
    $names = [];
    //Get service fixed
    $params = [
      'tokens' => [
        'docType' => $document,
        'clientId' => $number,
      ],
      'no_exception' => TRUE, //Next validate movil
    ];

    $name_fixed = $this->api->customerByCustomerId($params);

    if ($name_fixed) {
      $names['name_fixed'] = $name_fixed;
    }

    //Get service mobile
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $params_m = [
      'query' => [
        'clientType' => strtoupper((string)$document),
        'countInvoiceToReturn' => 1,
        'startDate' => '01%2F01%2F2000',
        'endDate' => "$day%2F$month%2F$year",
        'type' => 'mobile',
      ],
      'tokens' => [
        'clientId' => $number,
      ],
      'no_exception' => TRUE, //Next with validate
    ];

    $name_mobile = $this->api->getBillingInformation($params_m);

    if ($name_mobile) {
      $names['name_mobile'] = $name_mobile;
    }

    return $names;
  }

  /**
   * @param $company_data
   */
  public function _createCompany($company_data) {
    $company = CompanyEntityBo::create($company_data);
    $company->save();
  }

  /**
   * @param $data
   */
  public function _createUser($data) {
    //Create user
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
  }

  /**
   * @param $data_relation
   */
  public function _CreateCompanyUserRelation($data_relation) {
    //Create relations company-user
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
   * @param $phone_number
   * @param $sms_message
   */
  public function _sendSms($phone_number, $sms_message, $accion = '', $user_name = '', $company = '') {

    $params ['query'] = [
      'from' => 'Tigo',
      'to' => $phone_number,
      'text' => $sms_message,
    ];

    try {
      $sendSms = $this->api->sendSMS($params);
    }
    catch (\Exception $e) {
      \Drupal::logger('sendSms')->error('Phone ' . $phone_number . ' Error: ' . $e->getMessage());
      $service = \Drupal::service('tbo_core.audit_log_service');
      $service->loadName();
      //Create array data[]
      $data = [
        'event_type' => t('Cuenta'),
        'description' => t('Error en el envio del SMS'),
        'details' => 'Usuario ' . $service->getName() . ' presento error en ' . $accion . ' al enviar el mensaje al admin empresa ' . $user_name . ' de la empresa ' . $company . ', con numero de telefono ' . $phone_number,
      ];

      //Save audit log
      $service->insertGenericLog($data);
    }
  }

}