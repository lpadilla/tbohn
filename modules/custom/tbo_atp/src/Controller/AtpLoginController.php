<?php

namespace Drupal\tbo_atp\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AtpLoginController.
 *
 * @package Drupal\tbo_atp\Controller
 */
class AtpLoginController extends ControllerBase {

  protected $service;

	/**
	 * AtpLoginController constructor.
	 */
  public function __construct() {
    $this->service = \Drupal::service('tbo_atp.service_atp_login_controller');
  }

	/**
	 * @return redirect
	 */
  public function validateAtp() {
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();

  	return $this->service->validateAtp();
	}

}
