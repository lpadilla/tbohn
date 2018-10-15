<?php

namespace Drupal\tbo_account\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CompaniesListTigoAdminService.
 *
 * @package Drupal\tbo_account\Services
 */
class CompaniesListTigoAdminService {

  protected $service_message;
  protected $account_repository;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->account_repository = \Drupal::service('tbo_account.repository');
  }

  /**
   *
   */
  public function getFullName($uid = NULL) {
    $output = "";
    if (isset($uid)) {
      $database = \Drupal::database();
      $query = $database->select('users_field_data', 'user');
      $query->addField('user', 'full_name');
      $query->condition('user.uid', $uid);

      $output = $query->execute()->fetchAssoc();
    }

    return $output;
  }

  /**
   *
   */
  public function getTigoAdmins($uid = NULL) {
    $database = \Drupal::database();
    $query = $database->select('users_field_data', 'user');
    $query->innerJoin('user__roles', 'user_rol', 'user_rol.entity_id = user.uid');
    $query->addField('user', 'uid');
    $query->addField('user', 'full_name');
    if (isset($uid)) {
      $query->condition('user.uid', $uid, '<>');
    }
    $query->condition("user_rol.roles_target_id", 'tigo_admin');

    $lista = $query->execute()->fetchAllKeyed();

    return $lista;
  }

  /**
   *
   */
  public function updateTigoCompanies($tigoadmin, $companies = []) {
    // Validate is not empty $tigoadmin and $companies.
    if ($tigoadmin && !empty($companies)) {

      // Update companies.
      $num_updated = $this->account_repository->changeTigoAdminFromCompany($tigoadmin, $companies);

      $result = '';
      if ($num_updated) {
        $mensaje = $this->notification($tigoadmin, $companies);
        $result = $mensaje[0];

        // Save Audit log.
        $service_log = \Drupal::service('tbo_core.audit_log_service');
        $service_log->loadName();

        // Create array data[].
        $data = [
          'event_type' => 'Cuenta',
          'description' => 'Usuario asigna empresa a TigoAdmin',
          'details' => $mensaje[1],
        ];

        // Save audit log.
        $service_log->insertGenericLog($data);
      }

      return $result;

    }
    else {
      throw new HttpException(t('TigoAdmin wasn\'t provided'));
    }
  }

  /**
   * @param $tigoadmin
   * @param $companies
   * @return array
   */
  public function notification($tigoadmin, $companies) {
    // Get repository.
    $fields = [
      'full_name',
    ];
    $conditions = [
      'uid' => $tigoadmin,
    ];
    $tigoName = $this->account_repository->getUser($fields, $conditions);

    $current_user = \Drupal::currentUser();
    $conditions = [
      'uid' => $current_user->id(),
    ];
    $user_names = $this->account_repository->getUser($fields, $conditions);

    $output = '';
    $register = '';

    // Iterate all companies.
    foreach ($companies as $company => $newtigo) {
      $fields = [
        'full_name',
        'mail',
      ];
      $conditions = [
        'uid' => $newtigo,
      ];
      $tigoData = $this->account_repository->getUser($fields, $conditions, TRUE);

      $fields = [
        'company_name',
      ];
      $conditions = [
        'id' => $company,
      ];

      $company_name = $this->account_repository->getCompany($fields, $conditions, TRUE);

      $tokens['mail_to_send'] = $tigoData['mail'];
      $tokens['user'] = $tigoData['full_name'];
      $tokens['enterprise'] = $company_name['company_name'];

      $send = $this->service_message->send_message($tokens, 'assing_enterprise');

      $output .= t('La empresa @company_name fue asignada del usuario @tigoadmin al usuario @tigoData', ['@company_name' => $company_name['company_name'], '@tigoadmin' => $tigoName, '@tigoData' => $tigoData['full_name']]) . ".\n";
      $register .= t('Usuario @user reasignó la empresa @company_name de usuario @tigoadmin a usuario @tigoData', ['@user' => $user_names, '@company_name' => $company_name, '@tigoadmin' => $tigoName, '@tigoData' => $tigoData['full_name']]) . ".\n";

    }

    return [$output, $register];
  }

}
