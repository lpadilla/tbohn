<?php

namespace Drupal\tbo_core\Services;

use Drupal\user\Entity\User;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class AuditLogService.
 *
 * @package Drupal\tbo_core\Services
 */
class AuditLogService implements AuditLogInterface {

  private $storage;

  protected $name;

  protected $account;
  protected $api;

  /**
   * Constructor.
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
    $current_user = \Drupal::currentUser();
    $this->account = User::load($current_user->id());

    $full_name = $this->account->get('full_name');
    $this->name = (empty($full_name)) ? $current_user->getAccountName() : $full_name;
  }

  /**
   * Save the auditlogentity object in database.
   *
   * @param mixed $values
   *   Audit Log values.
   *
   * @return mixed
   *   Audit log id.
   */
  public function setAuditLog($values) {
    $entity = AuditLogEntity::create($values);
    $entity->save();
    return $entity->id();
  }

  /**
   * Get all audit logs entities with no format.
   *
   * @return mixed
   *   All audit logs.
   */
  public function getAllAuditLogs() {
    $query = \Drupal::service('entity.query')
      ->get('audit_log_entity');

    $entity_ids = $query->execute();
    $audit_log_entities = AuditLogEntity::loadMultiple($entity_ids);

    $logs = [];
    $log = new \StdClass();

    foreach ($audit_log_entities as $key => $value) {
      foreach ($value as $key2 => $value2) {
        $log->$key2 = $value2[0]->value;
      }
      array_push($logs, (array) $log);
    }
    return $logs;

  }

  /**
   * Get audit logs by filters formatted.
   *
   * @param mixed $filters
   *   Filters.
   * @param mixed $table_columns
   *   Table columns.
   * @param mixed $config_paginate
   *   Pagination configuration data.
   * @param mixed $date_config
   *   Date config.
   * @param mixed $export
   *   Export.
   *
   * @return array|ResourceResponse
   *   Resource response.
   */
  public function getAuditLogsByFilter($filters = NULL, $table_columns, $config_paginate = [], $date_config = '', $export = FALSE) {
    // Get repository.
    $account_repository = \Drupal::service('tbo_core.repository');

    if ($_GET['op'] && empty($config_paginate)) {
      // Segun el caso de uso para exportar masivos CU024 es de 40000.
      $config_paginate = [
        'number_pages' => '100',
        'number_rows_pages' => '400',
      ];

    }

    $result = $account_repository->getAuditLogsByFilter($filters, $table_columns, $config_paginate);

    $logs = [];
    $date_format = \Drupal::service('date.formatter');
    foreach ($result as $key => $content) {
      if (!$export) {
        if (isset($content->created)) {
          $content->created = $date_format->format(
            $content->created, 'custom', 'Y-m-d H:i:s'
          );
        }
      }

      array_push($logs, (array) $content);
    }

    // Validate not export.
    if (!$export) {
      // Save Audit log.
      $this->loadName();

      // Segun lo comentado con Nelly solo se guarda la empresa
      // si es un admin empresa o admin group.
      $roles = \Drupal::currentUser()->getRoles(TRUE);
      if ((in_array('admin_company', $roles) || in_array('admin_group', $roles)) && (!in_array('administrator', $roles) || !in_array('super_admin', $roles) || !in_array('tigo_admin', $roles))) {
        if (isset($_SESSION['company'])) {
          $companyName = $_SESSION['company']['name'];
          $companyDocument = $_SESSION['company']['nit'];
          $companySegment = isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '';
        }
        else {
          $companyName = 'No associated company';
          $companyDocument = 'NA';
          $companySegment = 'NA';
        }
      }

      // Create array data[].
      $data = [
        'companyName' => $companyName,
        'companyDocument' => $companyDocument,
        'companySegment' => $companySegment,
        'description' => 'Usuario consulta el log de auditoría',
        'event_type' => 'Cuenta',
        'details' => 'Usuario consulta el log de auditoría',
        'old_values' => '',
        'new_values' => '',
      ];

      // Save audit log.
      $this->insertGenericLog($data);
    }

    return $logs;
  }

  /**
   * Function to Insert log for all modules.
   *
   * @param mixed $data
   *   Audit Log parameters.
   */
  public function insertGenericLog($data) {
    // Get name role.
    $rol = \Drupal::service('tbo_core.repository')
      ->getRoleName($this->account->get('roles')->getValue()[0]['target_id']);

    $entity_values = [
      'user_names' => $this->name,
      'company_name' => isset($data['companyName']) ? $data['companyName'] : '',
      'company_document_number' => isset($data['companyDocument']) ? $data['companyDocument'] : '',
      'company_segment' => isset($data['companySegment']) ? $data['companySegment'] : '',
      'user_role' => $rol,
      'event_type' => isset($data['event_type']) ? $data['event_type'] : '',
      'description' => isset($data['description']) ? $data['description'] : '',
      'details' => isset($data['details']) ? $data['details'] : '',
      'old_values' => isset($data['old_value']) ? $data['old_value'] : '',
      'new_values' => isset($data['new_value']) ? $data['new_value'] : '',
      'technical_detail' => isset($data['technical_detail']) ? $data['technical_detail'] : '',
    ];

    $this->setAuditLog($entity_values);
  }

  /**
   * Load name.
   */
  public function loadName() {
    // Get user info.
    $current_user = \Drupal::currentUser();
    $get_account = $current_user->getAccount();
    $this->account = User::load($current_user->id());
    if (isset($get_account->full_name->value) && !empty($get_account->full_name->value)) {
      $this->name = $get_account->full_name->value;
    }
    elseif (isset($this->account->full_name->value) && !empty($this->account->full_name->value)) {
      $this->name = $this->account->full_name->value;
    }
    else {
      $this->name = $current_user->getAccountName();
    }
  }

  /**
   * Get name.
   */
  public function getName() {
    return $this->name;
  }

}
