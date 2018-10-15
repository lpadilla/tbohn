<?php

namespace Drupal\tbo_user\Services\Rest;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;

/**
 * Class EmulateSessionRestService.
 *
 * @package Drupal\tbo_user\Services\Rest
 */
class EmulateSessionRestService {

  protected $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Get config name.
    $request = $_GET;
    $config_name = $request['config_name'];

    // Get columns table.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_user');
    $columns_table = $tempstore->get($config_name . $request['config_columns']);
    $config_paginate = $tempstore->get($config_name . '_pager' . $request['config_columns']);

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');
    $result_query = $account_repository->getUserByCompaniesAndTigoAdmin($columns_table, $config_paginate);

    $repeat = $data2 = [];
    foreach ($result_query as $key => $data) {
      if (!array_key_exists($data->id, $repeat)) {
        array_push($data2, (array) $data);
        end($data2);
        $last_element = key($data2);
        if (isset($data2[$last_element]['name'])) {
          $data2[$last_element]['name'] = ucwords(strtolower($data2[$last_element]['name']));
        }
        $data2[$last_element]['admin_company'][] = (array) $data;
        $repeat[$data->id] = $last_element;
      }
      else {
        array_push($data2[$repeat[$data->id]]['admin_company'], (array) $data);
      }
    }
    return new ResourceResponse($data2);
  }

}
