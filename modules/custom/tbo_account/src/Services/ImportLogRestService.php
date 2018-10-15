<?php

namespace Drupal\tbo_account\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 */
class ImportLogRestService {

  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param $type
   * @param $fields
   * @param $status
   * @param $custom_id
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser, $type, $fields, $status, $custom_id) {

    $this->currentUser = $currentUser;

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $status = (isset($status)) ? explode(';', $status) : [];
    $data = [];

    if (!empty($type)) {
      if ($type == 'retrieve') {
        $data = $this->getData($fields);
      }
      else {
        if ($type == 'filter') {
          $data = $this->filteredData($status, $custom_id, $fields);
        }
        else {
          if ($type == 'unlock') {
            $data = \Drupal::config('adf_import_data.adfimportdataformconfig')
              ->get('import_finish');
          }
        }
      }
    }
    else {
      $data = 'To expective parameters';
    }

    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return new ResourceResponse($data);

  }

  /**
   * @param $fields
   *
   * @return array
   */
  protected function getData($fields) {

    $data_return = [];

    $fields = explode(';', $fields);
    $service = \Drupal::service('adf_import.export_service');
    $service->setTable('log_import_data_entity_field_data');
    $service->setCols($fields);
    $conditions = [];
    $data = $service->getDataLog($conditions, 0);

    // Pass stdObject to array.
    foreach ($data as $key => $value) {
      $data_return[] = json_decode(json_encode($value), TRUE);
    }
    return $data_return;
  }

  /**
   * @param $status_s
   * @param $status_f
   * @param $custom_id
   * @param $fields
   *
   * @return array
   */
  protected function filteredData($status, $custom_id, $fields) {
    $status_db = [
      'fallo' => 'Fallo',
      'exitoso' => 'Exitoso',
      'error' => 'Error',
    ];
    $data_return = [];
    // Set data for sql query.
    $fields = explode(';', $fields);
    $custom_id = (isset($custom_id)) ? $custom_id : FALSE;
    $status = (!empty($status)) ? $status : '';
    $service = \Drupal::service('adf_import.export_service');
    $service->setTable('log_import_data_entity_field_data');
    $service->setCols($fields);
    $conditions = [
    [
      'colum' => 'user_id',
      'value' => $this->currentUser->id(),
      'operator' => '=',
    ],
    ];

    if (!empty($status)) {
      $cant_status = count($status);
      if ($cant_status > 1) {
        foreach ($status as $st) {
          if (!empty($st)) {
            $conditions[] = [
              'colum' => 'status_import',
              'value' => $status_db[$st],
              'operator' => '=',
              'or' => 1,
            ];
          }
        }
      }
      elseif ($cant_status == 1 && !empty($status[0])) {
        $conditions[] = [
          'colum' => 'status_import',
          'value' => $status_db[$status[0]],
          'operator' => '=',
        ];
      }
    }

    if ($custom_id) {
      $conditions[] = [
        'colum' => 'custom_id',
        'value' => $custom_id,
        'operator' => '=',
      ];
    }

    // Pass stdObject to array.
    $data = $service->getDataLog($conditions, 0);
    foreach ($data as $key => $value) {
      $data_return[] = json_decode(json_encode($value), TRUE);
    }
    return $data_return;
  }

}
