<?php

namespace Drupal\tbo_billing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;

/**
 * Class BillingPaymentController.
 *
 * @package Drupal\tbo_billing\Controller
 */
class BillingPaymentController extends ControllerBase {
  protected $current_user;
  protected $tempStore;
  protected $segmentService;
  protected $segment;
  protected $api;
  protected $payment_service;
  protected $config;

  /**
   *
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user) {
    $this->tempStore = $temp_store_factory->get('tbo_billing');

    // Segment $var.
    $this->segmentService = \Drupal::service('adf_segment');
    $this->segmentService->segmentPhpInit();
    $this->segment = $this->segmentService->getSegmentPhp();
    $this->current_user = $current_user;
    $this->config = \Drupal::service('tbo_billing.service_billing_payment_controller');
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('current_user')
    );
  }

  /**
   * Payment.
   *
   * @return string
   */
  public function payment($type, $contractId, $invoiceRef, $monto = NULL, $fecha = NULL, $msisdn) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $url = $this->config->payment($type, $contractId, $invoiceRef, $monto, $fecha, $msisdn);
    return new TrustedRedirectResponse($url);
  }

  /**
   * Gateway.
   *
   * @return string
   */
  public function tigoGateway() {
    return new RedirectResponse($this->config->tigoGateway());
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function uneGateway() {
    return new RedirectResponse($this->config->uneGateway());
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function cancelUneGateway() {
    return new RedirectResponse($this->config->cancelUneGateway());
  }

}
