<?php

namespace Drupal\tbo_account\Services;

/**
 *
 */
class ImportLogExportService {

  private $currentUser;

  /**
   * ImportLogExportService constructor.
   */
  public function __construct() {
    $this->currentUser = \Drupal::currentUser();
  }

  /**
   * @return mixed
   */
  public function exportLog($type) {
    $type = (isset($type)) ? $type : 'csv';
    $date = new \DateTime('now');
    $date = $date->format('Y-m-d');
    $date1 = $date . " 00:00:00";
    $date2 = $date . " 23:59:59";
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $service = \Drupal::service('adf_import.export_service');
    $service->setTable('log_import_data_entity_field_data');
    $service->setCols(['custom_id', 'status_import', 'description']);
    $conditions = [
    ['colum' => 'user_id', 'value' => $this->currentUser->id(), 'operator' => '='],
    ['colum' => 'created', 'value' => $date1, 'operator' => '>='],
    ['colum' => 'created', 'value' => $date2, 'operator' => '<='],

    ];
    $params = [
      'headers' => ['Empresa', 'Estado', 'Descripción'],
      'type' => $type,
      'file_name' => 'log-massive',
      'conditions' => [],
    ];
    $response = $service->exportLogData($params);
    return $response;
  }

}
