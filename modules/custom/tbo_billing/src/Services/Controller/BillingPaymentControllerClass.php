<?php

namespace Drupal\tbo_billing\Services\Controller;

use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Url;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\user\PrivateTempStoreFactory;

/**
 * Manage config a 'BillingPaymentControllerClass'.
 */
class BillingPaymentControllerClass {
  protected $api;
  protected $tbo_config;
  protected $service_message;
  protected $repository;
  protected $service_log;
  protected $current_user;
  protected $tempStore;
  protected $segmentService;
  protected $segment;
  protected $payment_service;
  protected $config;

  /**
   * MultiplePaymentResponseControllerClass constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AccountInterface $current_user, PrivateTempStoreFactory $temp_store_factory) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->repository = \Drupal::service('tbo_billing.repository');
    $this->service_log = \Drupal::service('tbo_core.audit_log_service');
    $this->segmentService = \Drupal::service('adf_segment');
    $this->segmentService->segmentPhpInit();
    $this->segment = $this->segmentService->getSegmentPhp();
    $this->payment_service = \Drupal::service('tbo_billing.bill_payment');
    $this->current_user = $current_user;
    $this->tempStore = $temp_store_factory;
  }

  /**
   * Payment.
   *
   * @return string
   */
  public function payment($type, $contractId, $invoiceRef, $monto = NULL, $fecha = NULL, $msisdn) {
    $requestUrl = \Drupal::request()->server->get('HTTP_REFERER');
    $block = $this->createBlock();

    if (!isset($requestUrl)) {
      $requestUrl = $GLOBALS['base_url'] . '/detalle-factura';
    }

    if ($monto == 0) {
      $_SESSION['block_info']['type'] = 'ERROR-VALUE';
      $_SESSION['block_info']['render'] = $block;
      return $requestUrl;
    }

    $fecha = \Drupal::service('date.formatter')
      ->format(strtotime(urldecode($fecha)), 'custom', 'Y-m-d');

    $resultUrl = $this->payment_service->payBilling($contractId, $invoiceRef, $type, $monto, $fecha, $msisdn);

    /* Saving step 1 of payment process */
    $current_uri = \Drupal::request()->getRequestUri();

    if ($resultUrl !== FALSE) {
      $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_billing');
      $tempstore->set('RequestUrl', $requestUrl);
      return $resultUrl;
    }

    $_SESSION['block_info']['type'] = 'ERROR-PROCESS';
    $_SESSION['block_info']['render'] = $block;

    return $requestUrl;
  }

  /**
   * Gateway.
   *
   * @return string
   */
  public function tigoGateway() {
    $referenceCode = \Drupal::request()->query->get('referenceCode');
    $message = \Drupal::request()->query->get('message');
    $amount = \Drupal::request()->query->get('TX_VALUE');
    $replace = \Drupal::request()->query->get('TX_TAX');

    // Get tigo ID.
    $query = \Drupal::database()->select('openid_connect_authmap', 'open')
      ->fields('open', ['sub'])
      ->condition('uid', $this->current_user->id(), '=');

    $tigoId = $query->execute()->fetchField();
    $message_segment = '';
    if (isset($referenceCode) && isset($message)) {
      if ($message == 'DECLINED' || $message == 'EXPIRED') {
        $urlPayment = $this->tempStore->get('PaytransactionUrl-' . $referenceCode);
        if (!isset($urlPayment)) {
          // $urlPayment = '/tbo_billing/paymen  t/movil/'.$referenceCode.'/'.$referenceCode.'/11111/1111-11-11';.
          $urlPayment = BaseApiCache::get('payBilling', $referenceCode, '');
        }

        $_SESSION['block_info']['type'] = $message;
        $_SESSION['block_info']['environment'] = 'mobile';
        $_SESSION['block_info']['url'] = $urlPayment;

        $message_segment = 'Cancelado';
      }
      elseif ($message == 'PENDING') {
        $_SESSION['block_info']['type'] = $message;
        $_SESSION['block_info']['environment'] = 'mobile';
        $message_segment = 'Pendiente';
      }
      elseif ($message == 'APPROVED') {
        $_SESSION['block_info']['type'] = $message;
        $_SESSION['block_info']['environment'] = 'mobile';
        $message_segment = 'Confirmado';
      }
      else {
        // Redirige al home.
        $home = \Drupal::request()->getHost();
        $this->tempStore->get('RequestUrl')->delete('RequestUrl');
        return new RedirectResponse($home);
      }
      // Save audit log móvil.
      $type = t('móvil');
      $log = $this->payment_service->saveCheckoutAuditLog($type, $referenceCode, $message);

    }
    else {
      $message_segment = 'Cancelado';
      $_SESSION['block_info']['type'] = 'CANCEL';
      $_SESSION['block_info']['environment'] = 'mobile';
    }
    $delete_1 = $this->tempStore->get('PaytransactionUrl-' . $referenceCode);
    $delete_1 = $delete_1->delete('PaytransactionUrl-' . $referenceCode);
    $urlOrigin = $this->tempStore->get('RequestUrl');
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_billing');
    $url = $tempstore->get('RequestUrl');
    if (!isset($urlOrigin)) {
      $urlOrigin = '/factura-actual';
    }
    else {
      if (isset($url)) {
        $urlOrigin = $url;
      }
      else {
        $urlOrigin = '/detalle-factura';
      }
    }

    $amount = (empty($amount)) ? '' : (int) str_replace($replace, '', $amount);
    $category = 'Pasarela de Pago';
    $tigoId = (empty($tigoId)) ? '' : $tigoId;

    // Set segment track's.
    $this->segment->track([
      'event' => 'TBO - Retorno pasarela - Tx',
      'userId' => $tigoId,
      'properties' => [
        'category' => $category,
        'label' => $message_segment . ' - movil',
        'value' => empty($amount) ? 0 : $amount,
        'site' => 'NEW',
      ],
    ]);

    $paymentMethod = \Drupal::request()->query->get('lapPaymentMethodType');
    $paymentMethod = (empty($paymentMethod)) ? '' : $paymentMethod . ' - movil';
    if ($paymentMethod != '' && $amount != '') {
      $this->segment->track([
        'event' => 'TBO - Medio de pago',
        'userId' => $tigoId,
        'properties' => [
          'category' => $category,
          'label' => $paymentMethod,
          'value' => $amount,
          'site' => 'NEW',
        ],
      ]);
    }

    $franquicia = \Drupal::request()->query->get('lapPaymentMethod');
    if ($franquicia != 'PSE' && $franquicia != '') {
      $franquicia = (empty($franquicia)) ? '' : $franquicia . ' - movil';

      $this->segment->track([
        'event' => 'TBO - Franquicia',
        'userId' => $tigoId,
        'properties' => [
          'category' => $category,
          'label' => $franquicia,
          'value' => $amount,
          'site' => 'NEW',
        ],
      ]);
    }

    $bank = \Drupal::request()->query->get('pseBank');
    $bank = (empty($bank)) ? '' : $bank . ' - movil';
    if ($bank != '') {
      $this->segment->track([
        'event' => 'TBO - Entidad Bancaria',
        'userId' => $tigoId,
        'properties' => [
          'category' => $category,
          'label' => $bank,
          'value' => $amount,
          'site' => 'NEW',
        ],
      ]);
    }

    $_SESSION['block_info']['render'] = $this->createBlock();
    return $urlOrigin;
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function uneGateway() {
    if (isset($_SESSION['multiple_payment'])) {
      return Url::fromUri('internal:/tbo_billing/payment/multiple')->toString();
    }
    else {
      $api = \Drupal::service('tbo_api.client');

      $signature = $_SESSION['simple_payment']['response']->signature;
      $data = json_decode($_SESSION['simple_payment']['data']['body']);
      $status_response = FALSE;
      $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());

      $params = [
        'tokens' => [
          'signature' => $signature,
        ],
      ];

      try {
        $response = $api->validatePaymentWithSignature($params);
        $multiple__payment_reference = $response->payment_id;

        if ($response) {
          if ($response->state == 'success') {
            $status_response = TRUE;
            $amount = (int) $response->amount;

            $this->segment->track([
              'event' => 'TBO - Retorno pasarela',
              'userId' => $tigoId,
              'properties' => [
                'category' => 'Pasarela de Pago',
                'label' => 'Confirmado - fijo',
                'value' => $amount,
                'site' => 'NEW',
              ],
            ]);

            $this->segment->track([
              'event' => 'TBO - Medio de pago',
              'userId' => $tigoId,
              'properties' => [
                'category' => 'Pasarela de Pago',
                'label' => $response->pay_method . ' - fijo',
                'value' => $amount,
                'site' => 'NEW',
              ],
            ]);

            //Inválidamos la caché para el cliente específico.
            $company = $_SESSION['company'];
            BaseApiCache::invalidateTags([
              'findCustomerAccountsByIdentification:' . $company['nit'],
            ]);

            drupal_set_message(t("Factura pagada. </br>  Su factura han sido pagada con éxito"));
          }
        }
      }
      catch (\Exception $e) {
        // drupal_set_message(t("Su pago no se ha completado. </br> Algo ha salido mal y no se ha podido completar el pago de sus facturas, por favor intente nuevamente más tarde"), 'error');.
        $this->segment->track([
          'event' => 'TBO - Retorno pasarela',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pasarela de Pago',
            'label' => 'Cancelado - ' . $_SESSION['environment'],
            'value' => $data->paymentData->totalAmount,
            'site' => 'NEW',
          ],
        ]);
        UtilMessage::getMessage($e);
      }

      $contract = $_SESSION['simple_payment']['data']['contract'];
      $invoice = $_SESSION['simple_payment']['data']['invoice'];
      $query = \Drupal::database()->select('openid_connect_authmap', 'open')
        ->fields('open', ['sub'])
        ->condition('uid', $this->current_user->id(), '=');
      $tigoId = $query->execute()->fetchField();
      $tigoId = (empty($tigoId)) ? '' : $tigoId;
      $message = '';
      $category = 'Pasarela de Pago';

      if ($status_response) {
        $_SESSION['block_info']['type'] = 'APPROVED';
        $message = 'APPROVED';
      }
      else {
        $monto = (int) $data->paymentData->totalValueWithTax;
        $urlPayment = '/tbo_billing/payment/fijo/' . $contract . '/' . $invoice . '/' . $monto . '/' . $_SESSION['simple_payment']['data']['date'];
        $_SESSION['block_info']['type'] = 'DECLINED';
        $_SESSION['block_info']['url'] = $urlPayment;
        $message = 'DECLINED';
      }
      $_SESSION['block_info']['render'] = $this->createBlock();
      $urlOrigin = $this->tempStore->get('RequestUrl');
      if (!isset($urlOrigin)) {
        $urlOrigin = '/factura-actual';
      }
      else {
        $urlOrigin = '/detalle-factura';
      }

      // Save audit log fijo.
      $type = t('fijo');
      $log = $this->payment_service->saveCheckoutAuditLog($type, $invoice, $message);

      unset($_SESSION['simple_payment']);
      return $urlOrigin;
    }
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function cancelUneGateway() {

    $referenceCode = \Drupal::request()->query->get('paymentReference');
    $contractId = \Drupal::request()->query->get('contract');
    $amount = \Drupal::request()->query->get('amount');

    $_SESSION['block_info']['type'] = 'CANCEL';
    $_SESSION['block_info']['environment'] = 'mobile';
    $_SESSION['block_info']['render'] = $this->createBlock();

    $query = \Drupal::database()->select('openid_connect_authmap', 'open')
      ->fields('open', ['sub'])
      ->condition('uid', $this->current_user->id(), '=');
    $tigoId = $query->execute()->fetchField();

    $tigoId = (empty($tigoId)) ? '' : $tigoId;
    $amount = (empty($amount)) ? '' : (int) $amount;

    $params = [
      'tokens' => [
        'contractId' => $contractId,
        'invoiceId' => $referenceCode,
      ],
    ];

    try {
      $response = $this->api->checkoutByInvoiceId($params);

      // Set segment track's.
      $track1 = [
        'event' => 'TBO - Retorno pasarela',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Pasarela de Pago',
          'label' => 'Cancelado - fijo',
          'value' => $amount,
          'site' => 'NEW',
        ],
      ];

      $paymentType = $response->paymentMethods;
      $paymentType = (empty($paymentType)) ? '' : $paymentType;
      $track2 = [
        'event' => 'TBO - Medio de pago',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Pasarela de Pago',
          'label' => $paymentType . ' - fijo',
          'value' => $amount,
          'site' => 'NEW',
        ],
      ];

      // Send segment track's.
      $this->segment->track($track1);
      $this->segment->track($track2);
      if (isset($referenceCode)) {
        $this->tempStore->delete('PaytransactionUrl-' . $referenceCode);
      }
    }
    catch (\Exception $e) {
      $message = UtilMessage::getMessage($e);
      drupal_set_message($message['message'], 'error');
    }

    $urlOrigin = $this->tempStore->get('RequestUrl');
    $urlOriginDelete = $urlOrigin;
    if (!isset($urlOrigin)) {
      $urlOrigin = '/factura-actual';
    }
    else {
      $urlOrigin = 'detalle-factura';
    }
    $urlOriginDelete->delete('RequestUrl');

    return $urlOrigin;
  }

  /**
   *
   */
  public function createBlock() {
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance('response_payment_block', $config);
    // Some blocks might implement access check.
    $access_result = $plugin_block->access(\Drupal::currentUser());

    // Return empty render array if user doesn't have access.
    if (!$access_result) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No posee los permisos necesarios para acceder a este bloque'),
      ];
    }
    return $plugin_block;
  }

}
