<?php

namespace Drupal\tbo_general\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class GlobalFormatRestService.
 *
 * @package Drupal\tbo_general\Services
 */
class GlobalFormatRestService {

  private $tbo_config;
  private $currentUser;

  /**
   * GlobalFormatRestService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   */
  public function __construct(TboConfigServiceInterface $tbo_config) {
    $this->tbo_config = $tbo_config;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $value = '';

    if (isset($_GET['type']) && isset($_GET['value'])) {
      switch ($_GET['type']) {
        case 'money_value':
          $value = $this->tbo_config->formatCurrency($_GET['value']);
          break;
      }
    }

    return new ResourceResponse($value);
  }

}
