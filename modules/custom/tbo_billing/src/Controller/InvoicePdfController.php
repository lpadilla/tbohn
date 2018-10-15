<?php

namespace Drupal\tbo_billing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Class InvoicePdfController.
 *
 * @package Drupal\tbo_billing\Controller
 */
class InvoicePdfController extends ControllerBase {
  /**
   * @var configurationInstance\Drupal\tbo_billing\Plugin\Config\InvoicePdfControllerClass
   */
  protected $configurationInstance;

  /**
   * InvoicePdfController constructor.
   */
  public function __construct() {
    // Store our dependency.
    $this->configurationInstance = \Drupal::service('tbo_billing.service_invoice_pdf_controller');
  }

  /**
   * @param $contractNumber
   * @param string $type
   * @param null $param
   * @return string
   */
  public function generate($contractNumber, $type = 'movil', $param = NULL) {
    return $this->configurationInstance->generate($contractNumber, $type, $param);
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return string
   */
  public function access(AccountInterface $account) {
    return $this->configurationInstance->access($account);
  }

}
