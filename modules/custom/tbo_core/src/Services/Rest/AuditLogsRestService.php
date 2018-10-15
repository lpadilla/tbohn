<?php

namespace Drupal\tbo_core\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AuditLogsRestService.
 *
 * @package Drupal\tbo_core\Services\Rest
 */
class AuditLogsRestService {

  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    $this->currentUser = $currentUser;

    try {

      $request = $_GET;

      $filters = $request;
      unset($filters['_format']);
      unset($filters['config_columns']);

      // Get columns table.
      $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
      $columns_table = $tempstore->get('block_audit_logs_list_columns_' . $request['config_columns']);

      $data = $this->getQueryLogs($filters, $columns_table);
      $data2 = [];
      foreach ($data as $key => $content) {
        array_push($data2, (array) $content);
      }

      $aux_data = count($data2);
      for ($i = 0; $i < $aux_data; $i++) {

        if ($data2[$i]['status'] == 1) {
          $data2[$i]['status'] = "Activo";
        }
        else {
          $data2[$i]['status'] = "Inactivo";
        }
      }

    }
    catch (\Exception $e) {
      $response = new ResourceResponse("Error al intentar consultar informacion. Error : " . $e, 500);
      $response->setMaxAge(0);
      $response->setVary(time());
      return $response;
    }

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    return new ResourceResponse($data2);
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param $data
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $data) {
    $response = [];
    $this->currentUser = $currentUser;

    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Get request.
    $request = $data;

    try {
      foreach ($data as $key => $value) {

        if ($key == 'date_start' && $value > 0) {
          $data['date_range'][$key] = $value / 1000;
        }
        elseif ($key == 'date_end' && $value > 0) {
          $data['date_range'][$key] = ($value / 1000) + 86400;
        }
      };

      $filters = $data;

      unset($filters['date_start']);
      unset($filters['date_end']);
      unset($filters['config_columns']);
      unset($filters['config_name']);

      // Get config_name.
      $config_name = $request['config_name'];

      // Get columns table.
      $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
      $table_columns = $tempstore->get($config_name . $request['config_columns']);
      $config_paginate = $tempstore->get($config_name . '_pager' . $request['config_columns']);

      $save_config = \Drupal::service('config.factory')->getEditable('tbo_export.audit')
        ->set('table_columns', $table_columns)
        ->set('filters', ($filters == NULL) ? '' : $filters)
        ->save();

      $audit_log_service = \Drupal::service('tbo_core.audit_log_service');
      $result = $audit_log_service->getAuditLogsByFilter($filters, $table_columns, $config_paginate);

    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    return new ResourceResponse($result);
  }

}
