<?php

namespace Drupal\tbo_billing\Plugin\Config;

use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manage config a 'InvoicePdfController'.
 */
class InvoicePdfControllerClass {
  protected $api;
  protected $tbo_config;

  /**
   * InvoicePdfControllerClass constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /**
   * @param $contractNumber
   * @param movil|fijo $type
   * @param $param
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse|RedirectResponse
   */
  public function generate($contractNumber, $type = 'movil', $param = NULL) {

    $billingInfo = \Drupal::service('tbo_billing.download_pdf');
    $response = $billingInfo->getInvoicePdf($contractNumber, $type, $param);

    $urlResult = $response['url'];
    $errormsg = $response['errormsg'];

    if ($urlResult === FALSE) {
      drupal_set_message($errormsg, 'error');
      $request = Request::create(\Drupal::request()->server->get('HTTP_REFERER'));
      return new RedirectResponse($request->getRequestUri());
    }

    $response = new RedirectResponse($urlResult);
    $response->send();

  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account) {

    $user = \Drupal::currentUser()->getRoles(TRUE);
    $roles_autorized = ['admin_company', 'admin_company', 'admin_grupo', 'admin_group', 'super_admin', 'administrator'];
    foreach ($roles_autorized as $rol) {
      if (!in_array($rol, $user)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
