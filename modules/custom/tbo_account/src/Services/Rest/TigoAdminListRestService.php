<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;

/**
 * Class TigoAdminListRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class TigoAdminListRestService {

  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    return new ResourceResponse([]);
  }

  /**
   * @param $params
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $params) {
    $this->currentUser = $currentUser;

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    $service = \Drupal::service('tbo_account.tigo_admin_list');

    switch ($params['opt']) {
      case 1:
        // Get all data.
        $response = $service->getTigoAdminUsers($params);
        break;

      case 2:
        // Filter.
        $response = $service->filterUserTigo($params);
        break;

      case 3:
        // Update user tigo_admin.
        $response = $service->disableAdmin($params);
        break;
    }

    return new ResourceResponse($response);
  }

}
