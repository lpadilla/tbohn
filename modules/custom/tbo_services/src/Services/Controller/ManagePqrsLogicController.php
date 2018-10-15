<?php

namespace Drupal\tbo_services\Services\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ManagePqrsController.
 *
 * @package Drupal\tbo_services\Controller
 */
class ManagePqrsLogicController extends ControllerBase {

  /**
   * Implements redirectPqrs().
   *
   * @param $option
   *   The option url.
   * @param $url
   *   The url to redirect.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return data response.
   */
  public function redirectPqrs($option, $url) {
    if ($option == 'consultar' || $option == 'radicar') {
      $_SESSION['Pqrs']['option'] = $option;
      return new RedirectResponse(Url::fromUri('internal:/' . $url)->toString());
    }

    return new RedirectResponse(Url::fromUri('internal:/home')->toString());
  }

}
