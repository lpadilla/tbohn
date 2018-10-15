<?php

namespace Drupal\tbo_billing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class PaymentController.
 *
 * @package Drupal\tbo_billing\Controller
 */
class PaymentController extends ControllerBase {

  /**
   *
   */
  public function redirectionMovil() {}

  /**
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   */
  public function redirection() {
    $billing = \Drupal::service('tol.account.billing');
    $billingData = $billing->getBalanceDue();
    $account = \Drupal::currentUser();
    $email = $account->getEmail();
    $config = \Drupal::config('tol.settings');
    $url_gateway = $config->get('payment')['payment_url'];
    $msisdn = \Drupal::service('selfcare_core.session')->getCurrentId();
    $value = $billingData['original_result']->balance;
    $reference = $billingData['invoiceNumber'];
    $data['query'] = [
      'email' => $email,
      'value' => $value,
      'reference_code' => $reference,
      'language' => 'es',
      'currency' => 'COP',
      'scope' => 'access_checkout',
      'description' => t('Pago de Factura @reference_code|@msisdn', ['@reference_code' => $reference, '@msisdn' => $msisdn]) ,
      'msisdn' => $msisdn,
    ];
    $authorization_endpoint = Url::fromUri($url_gateway, $data)->toString(TRUE);
    $response = new TrustedRedirectResponse($authorization_endpoint->getGeneratedUrl());
    // Borrado de caché.
    Cache::invalidateTags(['tolapi:billing:' . $msisdn, "service:DebtStatus:$msisdn", "service:billingInvoices:$msisdn"]);
    unset($value);
    unset($data);
    unset($msisdn);
    unset($config);
    unset($contract);
    return $response;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return mixed
   */
  public function access(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }
    $currentType = \Drupal::service('selfcare_core.session')->getCurrentAccountType();
    if ($currentType == 'mobileid') {
      $billing = \Drupal::service('tol.account.billing');
      $response = $billing->getBalanceDue();
      $config = \Drupal::config('tol.settings');
      $min_value = $config->get('payment')['minimum_amount'];
      if ($response['original_result']->balance > $min_value) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

  /**
   * @return \Drupal\tbo_billing\Controller\RedirectResponse
   */
  public function response() {
    $config = \Drupal::config('tol.settings');
    $redirect_url = $config->get('payment')['redirect_url'];
    $status = isset($_GET['state']) ? $_GET['state'] : NULL;
    $type = 'status';
    switch ($status) {
      case 'APPROVED':
        $msg = 'Tu transacción ha sido aprobada. Pronto se reflejará el pago realizado';
        break;

      case 'PENDING':
        $msg = 'Tu transacción está pendiente de confirmación';
        break;

      case 'VALIDATION':
        $msg = 'Tu transacción ha sido aprobada. Pronto se reflejará el pago realizado';
        break;

      case 'REJECTED':
      case 'ERROR':
        $msg = 'Tu transacción ha rechazada. Intenta nuevamente más tarde';
        $type = 'error';
        break;
    }
    if (isset($msg)) {
      drupal_set_message(t($msg), $type);
    }
    if ($redirect_url == '<front>') {
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    else {
      return new RedirectResponse($redirect_url);
    }
  }

}
