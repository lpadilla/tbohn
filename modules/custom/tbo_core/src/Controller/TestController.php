<?php

namespace Drupal\tbo_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tbo_entities\Entity\CompanyEntity;

/**
 * Class TestController.
 *
 * @package Drupal\tbo_core\Controller
 */
class TestController extends ControllerBase {

  /**
   *
   */
  public function __construct() {
  }

  /**
   * @return array
   */
  public function setAuditLogsTest() {

    $company = CompanyEntity::load(1);
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $user_roles = $current_user->getRoles();

    $values = [
      'user_names' => 'este es el nombre',
      'company_name' => $company->getCompanyName(),
      'company_document_number' => $company->getDocumentNumber(),
      'company_segment' => 'este es el segmento',
      'user_role' => $user_roles,
      'auditoring_log_descripcion' => 'esta es la descripcion',
      'event_type' => 'cuenta',
      'auditoring_log_details' => 'este es el detalle',
      'old_values' => '1',
      'new_values' => '2',
    ];

    $audit_log_service = \Drupal::service('tbo_core.audit_log_service');
    $response = $audit_log_service->setAuditLog($values);
    // kint($response);
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Service AuditLog Implement method: ' . __FUNCTION__ . ' with response: ' . $response),
    ];
  }

  /**
   *
   */
  public function getAllAuditLogsTest() {

    $audit_log_service = \Drupal::service('tbo_core.audit_log_service');
    $entities = $audit_log_service->getAllAuditLogs();
    // kint($entities);
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Service AuditLog Implement method: ' . __FUNCTION__ . ' with response: ' . $entities),
    ];
  }

  /**
   *
   */
  public function auditLogsByFilterTest() {
    $params = [
      'company_name' => 'company',
      'company_segment' => 'segment',
      'user_names' => 'names',
      'user_role' => 'administrator',
      'description' => 'desc',
      'details' => 'det',
      'old_values' => '1',
      'new_values' => '2',

    ];

    $date_range = [
      'start_date' => '20/03/2017',
      'end_date' => '28/03/2017',
    ];
    $query = \Drupal::service('entity.query')
      ->get('audit_log_entity')
      ->condition('created', $date_range, 'BETWEEN')
      ->condition('company_name', $params['company_name'], 'CONTAINS')
      ->condition('company_segment', $params['company_segment'], 'CONTAINS')
      ->condition('user_names', $params['user_names'], 'CONTAINS')
      ->condition('user_role', $params['user_role'], 'CONTAINS')
      ->condition('description', $params['description'], 'CONTAINS')
      ->condition('details', $params['details'], 'CONTAINS')
      ->condition('old_values', $params['old_values'], 'CONTAINS')
      ->condition('new_values', $params['new_values'], 'CONTAINS');

    $audit_log_service = \Drupal::service('tbo_core.audit_log_service');
    $entities = $audit_log_service->getAuditLogsByFilter();
    // kint($entities);
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Service AuditLog Implements method: ' . __FUNCTION__ . ' with response: ' . $entities),
    ];
  }

}
