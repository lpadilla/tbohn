<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class UserListRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class UserListRestService {

  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param $data
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $data) {
    $response = [];
    $this->currentUser = $currentUser;

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $users_service = \Drupal::service('tbo_account.users');
      $response = $users_service->getUsersByFilterNew($data);
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }
    return new ResourceResponse($response);
  }

}
