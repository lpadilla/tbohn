<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CompleteEnterprisesRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class CompleteEnterprisesRestService {

  protected $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->currentUser->hasPermission('access list company')) {
      throw new AccessDeniedHttpException();
    }

    // Init response.
    $response = [];

    // Define $vars for the search.
    $search = $_GET['autocomplete'];

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');
    $saveData = $account_repository->getCompanyByAutocomplete($search);

    foreach ($saveData as $key => $value) {
      $response[$key] = json_decode(json_encode($value), TRUE);
    }

    // Return data.
    return new ResourceResponse($response);
  }

}
