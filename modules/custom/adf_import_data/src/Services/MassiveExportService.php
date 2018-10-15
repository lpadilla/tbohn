<?php

namespace Drupal\adf_import_data\Services;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Class MassiveExportService.
 */
class MassiveExportService implements MassiveExportInterface {

  protected $cols;

  protected $table;

  /**
   *
   */
  public function __construct() {
    $this->table = 'log_import_data_entity_field_data';
    $this->cols = ['custom_id', 'status_import', 'description'];
  }

  /**
   * @return int
   */
  public function countAllRecord() {
    $db = \Drupal::database();
    $rows = $db->query("SELECT * FROM $this->table");
    $rows->allowRowCount = TRUE;
    $num_rows = 0;
    if ($rows->rowCount() > 0) {
      $num_rows = $rows->rowCount();
    }
    return $num_rows;
  }

  /**
   * @param $params
   *
   * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function exportLogData($params) {

    $service = '\Drupal\adf_import_data\Services\MassiveExportService';
    $batch = [
      'title' => t('Exportación de datos'),
      'operations' => [
        [[$service, 'logMassiveGetData'], [$params]],
        [[$service, 'logMassiveExportData'], [$params]],
      ],
      'progress_message' => t('Se ha completado un @percentage%'),
      'progressive' => TRUE,
      'init_message' => t('El proceso de exportación está inciando'),
      'error_message' => t('Ha ocurrido un error al exportar el archivo.'),
      'finished' => [$service, 'logMassiveBatchFinished'],

    ];

    batch_set($batch);
    $export = 'export-import';
    return batch_process('adf_core/download/export/' . $export);
  }

  /**
   * @param $conditions
   * @param $start
   * @param int $limit
   * @return array
   */
  public function getDataLog($conditions, $start, $limit = 0) {
    $result = [];
    $query = \Drupal::database()->select($this->table, 't');
    $query->fields('t', $this->cols);
    $or = [];

    // Set query and.
    foreach ($conditions as $key => $condition) {
      if (!empty($condition['or'])) {
        $or[] = $key;
      }
      else {
        $query->condition('t.' . $condition['colum'], $condition['value'], $condition['operator']);
      }
    }

    // If you need or set $columns['or'].
    if (!empty($or)) {
      $group = $query->orConditionGroup();
      foreach ($or as $key_value => $value) {
        $group->condition('t.' . $conditions[$value]['colum'], $conditions[$value]['value'], $conditions[$value]['operator']);
      }
      $query->condition($group);
    }

    $data = $query->execute()->fetchAll();
    if ($data) {
      $result = $data;
    }
    return $result;
  }

  /**
   * @param $params
   * @param $context
   */
  public function logMassiveGetData($params, &$context) {

    $context['results']['empty'] = 0;
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = 0;
    $context['finished'] = 0;
    $service = \Drupal::service('adf_import.export_service');
    $cant = $service->countAllRecord();
    $data_query = $service->getDataLog($params['conditions'], 0);
    $context['results']['data'] = $data_query;
    $context['results']['cantElement'] = $cant;
    $context['results']['headers'] = $params['headers'];
    $context['finished'] = 1;
    if (empty($data_query)) {
      $context['results']['empty'] = 1;
    }

  }

  /**
   * @param $params
   * @param $context
   */
  public function logMassiveExportData($params, &$context) {

    if ($context['results']['empty'] == 0) {
      $user = \Drupal::currentUser();
      $time = date('Y-m-d');
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_number'] = 0;
      $context['sandbox']['max'] = $context['results']['cantElement'];
      $context['finished'] = 0;
      $context['message'] = t('Exportando Filas...');

      $type = $params['type'];
      if ($type == "xls") {
        $type = "xlsx";
      }
      $data_export = $context['results']['data'];
      $data_headers = $context['results']['headers'];

      $directory = 'public://private/';
      file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

      $dir = \Drupal::service('stream_wrapper_manager')
        ->getViaUri('public://private/')
        ->realpath();
      $file_name = $params['file_name'] . '-' . $time . $user->id() . '.' . $type;
      $path = $dir . '/' . $file_name;
      $header = '';

      if ($type == 'txt') {

        $file = fopen($path, 'w');
        $header = 'text/plain';
        $file_header = implode(' ', $data_headers);
        fwrite($file, $file_header . "\r\n");
        foreach ($data_export as $row) {
          $line = "";
          foreach ($row as $r) {
            $line .= $r . ';';
          }
          $line = substr($line, 0, -1);
          fwrite($file, $line . "\r\n");
          $context['sandbox']['progress']++;
          $context['message'] = t('Exportando información %procces de %max', [
            '%procces' => $context['sandbox']['progress'],
            '%max' => $context['sandbox']['max'],
          ]);
          if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
            $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
          }
        }

        if (fclose($file)) {
          $context['results']['file_success'] = TRUE;
        }
        else {
          $context['results']['file_success'] = FALSE;
        }

      }

      // Export for xlsx and csv formats.
      if ($type == 'xlsx' || $type == 'csv') {
        $writer = ($type == 'xlsx') ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
        $writer->openToFile($path);

        if ($type == 'xlsx') {
          $header = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
          $writer->getCurrentSheet()->setName($params['file_name']);
        }

        if ($type == 'csv') {
          $header = 'text/csv';
          $writer->setFieldDelimiter(';');
        }

        $writer->addRow($data_headers);
        foreach ($data_export as $row) {
          $element_row = [];
          foreach ($row as $r) {
            $element_row[] = $r;
          }
          $writer->addRow($element_row);
          $context['sandbox']['progress']++;
          $context['message'] = t('Exportando información %procces de %max', [
            '%procces' => $context['sandbox']['progress'],
            '%max' => $context['sandbox']['max'],
          ]);
        }

        if ($writer->close()) {
          $context['results']['file_success'] = TRUE;
        }
        else {
          $context['results']['file_success'] = FALSE;
        }
      }

      $_SESSION['export_download']['path'] = $path;
      $_SESSION['export_download']['file_name'] = $file_name;
      $_SESSION['export_download']['header_doc'] = $header;
      $_SESSION['export_download']['empty'] = FALSE;

    }
    else {
      $_SESSION['export_download']['empty'] = TRUE;
    }

    // Add session var.
    $tempstore = \Drupal::service('user.private_tempstore')->get('adf_core');
    $tempstore->set('adf_core_download', $_SESSION['export_download']);

    // Batch save data.
    $context['results']['header_doc'] = $header;
    $context['results']['path'] = $path;
    $context['results']['file_name'] = $file_name;
    $context['finished'] = 1;
    $context['results']['empty'] = $_SESSION['export_download']['empty'];
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   */
  public function logMassiveBatchFinished($success, $results, $operations) {
    if ($results['empty'] != 0) {
      drupal_set_message('No hay información para exportar', 'status');
    }
  }

  /**
   * @return array
   */
  public function getCols() {
    return $this->cols;
  }

  /**
   * @param array $cols
   */
  public function setCols(array $cols) {
    $this->cols = $cols;
  }

  /**
   * @return string
   */
  public function getTable() {
    return $this->table;
  }

  /**
   * @param string $table
   */
  public function setTable($table) {
    $this->table = $table;
  }

}
