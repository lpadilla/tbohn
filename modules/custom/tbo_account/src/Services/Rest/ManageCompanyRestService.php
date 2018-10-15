<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ManageCompanyRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class ManageCompanyRestService {

  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $data2 = [];

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access list company')) {
      throw new AccessDeniedHttpException();
    }

    $filters = $_GET;

    if (isset($_GET['q'])) {
      $q = $_GET['q'];
    }

    $config_name = $filters['config_name'];
    // Get columns table.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
    $columns_table = $tempstore->get($config_name . $filters['config_columns']);
    $config_paginate = $tempstore->get($config_name . '_pager' . $filters['config_columns']);

    unset($filters['_format']);
    unset($filters['config_columns']);
    unset($filters['config_name']);
    unset($filters['filter']);
    if (isset($q)) {
      unset($filters['q']);
    }
    if (isset($columns_table['delete'])) {
      unset($columns_table['delete']);
    }

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');

    // If exists filters.
    if ($_GET['filter'] && !empty($filters)) {
      // Get data companies and format to return.
      try {
        $data = $account_repository->getQueryOnlyTableCompanies($filters, $columns_table, $config_paginate, $this->currentUser->id());
      }
      catch (\Exception $e) {
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
    }

    // If filters no found then get all data.
    try {
      $data = $account_repository->getQueryOnlyTableCompanies($filters, $columns_table, $config_paginate, $this->currentUser->id());
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    $data2 = [];
    foreach ($data as $key => $content) {
      array_push($data2, (array) $content);
    }

    // Return data.
    return new ResourceResponse($data2);
  }

}
