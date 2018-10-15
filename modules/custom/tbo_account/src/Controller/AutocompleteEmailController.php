<?php

namespace Drupal\tbo_account\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AutocompleteEmailController.
 *
 * @package Drupal\tbo_account\Controller
 */
class AutocompleteEmailController extends ControllerBase {

  protected $service_controller;

  /**
   * TboAccountController constructor.
   */
  public function __construct() {
    $this->service_controller = \Drupal::service('tbo_account.tbo_account_controller_autocomplete_service');
  }

  /**
   * Autocompleteemail.
   *
   * @return string
   *   Return Hello string.
   */
  public function autocompleteEmail($mail) {
    return $this->service_controller->autocompleteEmail($mail);
  }

}
