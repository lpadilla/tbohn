<?php

namespace Drupal\adf_import_data\Services;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_import_data\Entity\LogImportDataEntity;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;

/**
 * Class MassiveImportService.
 *
 * @package Drupal\adf_import_data
 */
class MassiveImportService {

  protected $fileUsage;

  protected $cols;

  /**
   * {@inheritdoc}
   */
  public function __construct(DatabaseFileUsageBackend $file_usage) {
    $this->fileUsage = $file_usage;
    $this->cols = ['custom_id', 'status_import', 'description'];
  }

  /**
   * @param $pre_batch
   * @param $data_value
   *
   * @return mixed
   *
   * @param  $data_value
   *   ['file'] need Drupal\file\Entity\File object
   */
  public function setImportBatch($pre_batch, $data_value) {

    $config = \Drupal::config('adf_import_data.import_config');
    $pages = $config->get('cant_element');
    $cache_time = $config->get('cache_time');
    $elements = 0;
    // Read and process file.
    $reader = ReaderFactory::create(Type::CSV);
    $reader->setEncoding('UTF-8');
    $reader->open($data_value['url']);
    $operations = $pre_batch['operations'];
    unset($pre_batch['operations']);

    $divade = 0;
    $count_save = 0;
    $save_pages = $pages;
    $file_data = [];
    foreach ($reader->getSheetIterator() as $sheet) {

      foreach ($sheet->getRowIterator() as $row_key => $row) {
        if ($row_key != 1) {
          $validate_caracteres = preg_match('/^[A-Za-z0-9 @;,.áéíóúñüÁÉÍÓÚÑÜ_-]*$/', $row[0]);

          if ($validate_caracteres) {
            $row_utf8 = $row[0];
          }
          else {
            $row_utf8 = utf8_encode($row[0]);
          }

          $file_data['part' . $divade][$row_key] = explode(";", $row_utf8);

          $count_save++;

          if ($count_save == $pages || empty($row) || !isset($row)) {
            $divade++;
            $pages = $pages + $save_pages;
          }
          $elements++;
        }
      }
    }
    $reader->close();
    if (empty($file_data)) {
      return FALSE;
    }

    // Set paginate data for someone operation.
    foreach ($file_data as $key_file => $value_file) {
      foreach ($operations as $key_op => $value_op) {
        $value_op[1][] = $value_file;
        $pre_batch['operations'][] = $value_op;
      }
    }
    // \Drupal::logger('$file_data')->notice(print_r($file_data, TRUE));
    // delete file.
    $this->fileUsage->delete($data_value['file'], $data_value['module'], $data_value['id'], $data_value['count']);
    file_delete($data_value['fid']);

    // Set batch default values.
    if (empty($pre_batch['title'])) {
      $pre_batch['title'] = t('Procesando datos...');
    }

    if (empty($pre_batch['init_message'])) {
      $pre_batch['init_message'] = t('Iniciando proceso');
    }

    if (empty($pre_batch['progress_message'])) {
      $pre_batch['progress_message'] = t('Procesando: Se ha completado un @percentage%');
    }

    if (empty($pre_batch['error_message'])) {
      $pre_batch['error_message'] = t('Ha ocurrido un error durante el procesamiento de los datos.');
    }

    if (empty($pre_batch['library'])) {
      $pre_batch['library'] = '';
    }
    return $pre_batch;
  }

  /**
   * @param $keys
   * @param null $key
   */
  public function getCacheData($keys) {
    $data = [];
    foreach ($keys as $key => $value) {
      $data[] = BaseApiCache::getGlobal($value);
    }

    return $data;
  }

  /**
   * @param $data
   * @param bool $convert
   * @return \Drupal\Core\Entity\EntityInterface|static
   */
  public function insertDataLog($data, $convert = FALSE) {
    if ($convert) {
      $data = $this->convertArray($data);
    }
    $set_log = LogImportDataEntity::create($data);
    $set_log->save();
  }

  /**
   * @param $data
   * @param bool $convert
   */
  public function insertMultipleDataLog($data, $convert = FALSE) {
    foreach ($data as $dat) {
      $this->insertDataLog($dat, $convert);
    }
  }

  /**
   * @param $data
   * @return array
   */
  public function convertArray($data) {
    $result = [];
    $i = 0;
    foreach ($this->cols as $col) {
      $result[$col] = $data[$i];
      $i++;
    }
    return $result;

  }

  /**
   * @param $table
   */
  public function deleteLogTable($table) {
    $query = \Drupal::database()->delete($table);
    $query->condition('user_id', 0, '>');
    $query->execute();
  }

}
