<?php

namespace Drupal\tigoid_migrate\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class MigrateController.
 *
 * @package Drupal\tigoid_migrate\Controller
 */
class LoginController extends ControllerBase {

  /**
   *
   * @return string
   */
  public function initLogin() {

    $build = [
      '#theme' => 'login_form',
      '#fields' => [],
      '#attached' => [
        'library' => [
          'tigoid_migrate/tigoid_migrate',
        ],
      ],

    ];

    return $build;

  }

}
