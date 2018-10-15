<?php

namespace Drupal\tbo_billing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Class MultiplePaymentResponseController.
 *
 * @package Drupal\tbo_billing\Controller
 */
class MultiplePaymentResponseController extends ControllerBase {
  /**
   * @var configurationInstance\Drupal\tbo_billing\Plugin\Config\MultiplePaymentResponseControllerClass
   */
  protected $configurationInstance;

  /**
   * MultiplePaymentResponseController constructor.
   */
  public function __construct() {
    // Store our dependency.
    $this->configurationInstance = \Drupal::service('tbo_billing.service_multiple_payment_controller');
  }

  /**
   * @return string
   */
  public function generate() {
    return $this->configurationInstance->generate();
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return string
   */
  public function access(AccountInterface $account) {
    return $this->configurationInstance->access($account);
  }

}
