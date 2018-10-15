<?php

namespace Drupal\tbo_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CompanySelectorController.
 *
 * @package Drupal\tbo_general\Controller
 */
class CompanySelectorController extends ControllerBase {

  protected $configClass;

  /**
   * CompanySelectorController's constructor.
   */
  public function __construct() {
    $this->configClass = \Drupal::service('tbo_general.company_selector_controller');
  }

  /**
   * Companyselector.
   */
  public function companySelector($uid) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $response = $this->configClass->companySelector($uid);
    $result = [];

    switch ($response['type']) {
      case 'url':
        $result = new RedirectResponse($response['data']);
        break;

      case 'other':
        $result = $response['data'];
        break;
    }

    return $result;
  }

}
