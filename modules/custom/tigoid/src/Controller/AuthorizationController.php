<?php

namespace Drupal\tigoid\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AuthorizationController.
 *
 * @package Drupal\tigoid\Controller
 */
class AuthorizationController extends ControllerBase {

  protected $configClass;
  protected $clientName;
  protected $configuration;

  /**
   *
   */
  public function __construct() {
    $this->configClass = \Drupal::service('tigoid.authorization_controller');
    $this->clientName = "tigoid";
    $this->configuration = $this->config('openid_connect.settings.' . $this->clientName);
  }

  /**
   * Validation line method.
   */
  public function authorizeValidationLine($msisdn) {
    $client = $this->configClass->authorizeValidationLine($msisdn, $this->configuration, $this->clientName);
    return $client;
  }

  /**
   * Return Json Response with HE URL Redirect.
   *
   * @return string
   *   Return Hello string.
   */
  public function authorizeHe() {
    $response = $this->configClass->authorizeHe($this->configuration, $this->clientName);
    return $response;

  }

  /**
   * Authorize method.
   */
  public function authorize() {
    $client = $this->configClass->authorize($this->configuration, $this->clientName);
    return $client;
  }

}
