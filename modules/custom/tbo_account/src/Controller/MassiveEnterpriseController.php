<?php

namespace Drupal\tbo_account\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Class MassiveEnterpriseController.
 *
 * @package Drupal\tbo_account\Controller
 */
class MassiveEnterpriseController extends ControllerBase {

  /**
   * @var configurationInstance\Drupal\tbo_billing\Plugin\Config\InvoicePdfControllerClass
   */
  protected $configurationInstance;

  /**
   * InvoicePdfController constructor.
   */
  public function __construct() {
    // Store our dependency.
    $this->configurationInstance = \Drupal::service('tbo_account.import_log_export');
  }

  /**
   * @return mixed
   */
  public function exportLog() {
    return $this->configurationInstance->exportLog($_GET['type-file']);
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return string
   */
  public function access(AccountInterface $account) {
    return $this->configurationInstance->access($account);
  }

}
