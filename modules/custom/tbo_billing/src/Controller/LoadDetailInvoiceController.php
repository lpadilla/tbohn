<?php

namespace Drupal\tbo_billing\Controller;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\tbo_general\Services\TboConfigService;

/**
 * Class LoadDetaillInvoiceController.
 *
 * @package Drupal\tbo_billing\Controller
 */
class LoadDetailInvoiceController extends ControllerBase {

  private $tboConfigService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $tboConfigService = $container->get('tbo_general.tbo_config');
    return new static($tboConfigService);
  }

  /**
   *
   */
  public function __construct(TboConfigService $tboConfigService) {
    $this->tboConfigService = $tboConfigService;
  }

  /**
   * @param $contractId
   * @param $invoiceId
   */
  public function payment($contractId, $invoiceId) {
    // Save log audit.
    $this->_saveHistoryInvoiceDetail($invoiceId);

    $service = \Drupal::service('tbo_api.client');

    $params['tokens'] = [
      'contractId' => $contractId,
      'billId' => $invoiceId,
    ];

    $response = NULL;

    try {
      $data = $service->findCustomerPaymentByAccountIdAndBillNo($params);
    }
    catch (\Exception $e) {
      $mensaje = UtilMessage::getMessage($e);
      $response->error = $mensaje['message'];
    }

    if ($data) {
      $paymentDueDate_exp = str_replace('/', '-', $data->paymentDay);
      $response = $data;
      $response->paymentDay = $this->tboConfigService->formatDate(strtotime($paymentDueDate_exp));
    }

    $twig = \Drupal::service('twig');
    $template = $twig->loadTemplate(drupal_get_path('module', 'tbo_billing') . '/templates/block--invoices-detail.html.twig');
    echo $template->render(['data' => $response]);
    die;
  }

  /**
   * @param $client
   */
  public function _saveHistoryInvoiceDetail($invoiceId) {
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturacion',
      'description' => $this->t('Usuario accede al historico de factura'),
      'details' => 'Usuario ' . $service->getName() . ' accedió  al  historico  de  la  factura ' . $invoiceId,
      'old_value' => '',
      'new_value' => '',
    ];

    // Save audit log.
    $service->insertGenericLog($data);
  }

}
