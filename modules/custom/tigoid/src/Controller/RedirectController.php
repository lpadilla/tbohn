<?php

namespace Drupal\tigoid\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

// Use Drupal\user_lines\UserLines;.
define("TIGOID_REDIRECT_ONDEMAND", 1);
define("TIGOID_REDIRECT_HE", 2);
define("TIGOID_REDIRECT_VALIDATION_LINE", 3);
define("TIGOID_SIGNUP_AUTHENTICATION_TYPE", 0);
define("TIGOID_SIGNIN_AUTHENTICATION_TYPE", 1);
define("TIGOID_MIGRATE_AUTHENTICATION_TYPE", 2);
define("TIGOID_UNKNOWN_AUTHENTICATION_TYPE", 3);

/**
 * Class RedirectController.
 *
 * @package Drupal\tigoid\Controller
 */
class RedirectController extends ControllerBase implements AccessInterface {

  /**
   * @var \Drupal\tigoid\Services\Controller\RedirectControllerService
   */
  protected $configClass;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->configClass = \Drupal::service('tigoid.redirect_controller');
  }

  /**
   * Access callback: Redirect page.
   *
   * @return bool
   *   Whether the state token matches the previously created one that is stored
   *   in the session.
   */
  public function access() {
    $status = $this->configClass->access();

    if ($status) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Redirect.
   *
   * @param string $client_name
   *   The client name.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response starting the authentication request.
   */
  public function authenticate($client_name = '') {
    $client_name = "tigoid";
    $configuration = $this->config('openid_connect.settings.' . $client_name)
      ->get('settings');
    $url = $this->configClass->authenticate($client_name, $configuration);
    return new RedirectResponse($url);
  }

}
