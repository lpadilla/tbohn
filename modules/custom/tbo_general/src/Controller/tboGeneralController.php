<?php

namespace Drupal\tbo_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class tboGeneralController extends ControllerBase {

  /**
   * Implements function for redirect search to tigo site.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   */
  public function searchB2b(Request $request) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $url = \Drupal::config('tbo_general.search_b2b_config_form')->get('url');
    $search = str_replace('{data}', $_GET['keys'], $url);
    return new TrustedRedirectResponse($search);
  }

}
