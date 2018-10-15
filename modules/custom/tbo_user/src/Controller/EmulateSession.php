<?php

namespace Drupal\tbo_user\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class EmulateSession.
 *
 * @package Drupal\tbo_user\Controller
 */
class EmulateSession extends ControllerBase {
  protected $instance;

  /**
   *
   */
  public function __construct() {
    $this->instance = \Drupal::service('tbo_user.emulate_admin_company_session_controller');
  }

  /**
   *
   */
  public function emulateSession($user) {
    return $this->instance->emulateSession($user);
  }

}
