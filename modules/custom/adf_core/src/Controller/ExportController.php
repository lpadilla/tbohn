<?php

namespace Drupal\adf_core\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ExportController.
 */
class ExportController extends ControllerBase {

  /**
   * @var mixed
   */
  private $configuration_instance;

  /**
   *
   */
  public function __construct() {
    $this->configuration_instance = \Drupal::service('adf_core.export_controller');
  }

  /**
   * Export data.
   *
   * @param $type
   * @param $export
   * @param $reload
   * @param $uuid
   *
   * @return mixed
   */
  public function exportData($type, $export, $reload, $uuid) {
    return $this->configuration_instance->exportData($type, $export, $reload, $uuid);
  }

  /**
   * @param $export
   * @return mixed
   */
  public function downloadExportData($export) {
    return $this->configuration_instance->downloadExportData($export);
  }

  /**
   * @param $file_name
   * @param null $directory
   * @return mixed
   */
  public function downloadPublic($file_name, $directory = NULL) {
    return $this->configuration_instance->downloadPublic($file_name, $directory);
  }

}
