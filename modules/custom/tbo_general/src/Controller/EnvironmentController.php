<?php

namespace Drupal\tbo_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class EnvironmentController.
 *
 * @package Drupal\tbo_general\Controller
 */
class EnvironmentController extends ControllerBase {

  /**
   *
   */
  public function changeEnvironment($type) {
    $request = \Drupal::request();
    if ($type == 'movil') {
      $_SESSION['environment'] = 'movil';
      if (isset($_SESSION['company'])) {
        $_SESSION['environment_' . $_SESSION['company']['nit']] = 'movil';
      }
    }
    elseif ($type == 'fijo') {
      $_SESSION['environment'] = 'fijo';
      if (isset($_SESSION['company'])) {
        $_SESSION['environment_' . $_SESSION['company']['nit']] = 'movil';
      }
    }

    $url = '';

    if (isset($_GET['paymentM'])) {
      $url = 'internal:/factura-actual?paymentM=' . $_GET['paymentM'];
    }
    else {
      $url = 'internal:/factura-actual';
    }
    // Return referer path
    // return new RedirectResponse($request->headers->get('referer'));.
    return new RedirectResponse(Url::fromUri($url)
      ->toString());
  }

}
