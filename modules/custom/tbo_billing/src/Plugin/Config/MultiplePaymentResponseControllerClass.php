<?php

namespace Drupal\tbo_billing\Plugin\Config;

use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\adf_core\Base\BaseApiCache;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Drupal\Core\Url;

/**
 * Manage config a 'MultiplePaymentResponseController'.
 */
class MultiplePaymentResponseControllerClass {
  protected $api;
  protected $tbo_config;
  protected $service_message;
  protected $repository;
  protected $service_log;
  protected $segmentService;
  protected $segment;

  /**
   * MultiplePaymentResponseControllerClass constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->service_message = \Drupal::service('tbo_mail.send');
    $this->repository = \Drupal::service('tbo_billing.repository');
    $this->service_log = \Drupal::service('tbo_core.audit_log_service');
    $this->segmentService = \Drupal::service('adf_segment');
    $this->segmentService->segmentPhpInit();
    $this->segment = $this->segmentService->getSegmentPhp();
  }

  /**
   * @return \Drupal\Core\Routing\TrustedRedirectResponse|RedirectResponse
   */
  public function generate() {
    $signature = $_SESSION['multiple_payment']['response']->signature;
    $status_response = FALSE;

    $params = [
      'tokens' => [
        'signature' => $signature,
      ],
    ];

    $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
  
    $amount = 0;
    foreach ($_SESSION['multiple_payment']['data'] as $value) {
      $amount += $value['invoice_value'];
    }
    
    try {
      $response = $this->api->validatePaymentWithSignature($params);
      $multiple__payment_reference = $response->payment_id;

      if ($response) {
        $type = $response->pay_method != 'PSE' ? 'Tarjeta de Crédito' : $response->pay_method;
        $this->segment->track([
          'event' => 'TBO - Medio de Pago Multi',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pasarela de Pago',
            'label' => $type . ' - ' . $_SESSION['environment'],
            'value' => (int) $response->amount,
            'site' => 'NEW',
          ],
        ]);

        $this->segment->track([
          'event' => 'TBO - Entidad Bancaria Multi',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pasarela de Pago',
            'label' => $response->bankName . ' - ' . $_SESSION['environment'],
            'value' => (int) $response->amount,
            'site' => 'NEW',
          ],
        ]);
  
        $this->segment->track([
          'event' => 'TBO - Retorno pasarela Multi - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Pasarela de Pago',
            'label' => 'Confirmado - ' . count($_SESSION['multiple_payment']['data']) . ' - ' . $_SESSION['environment'],
            'value' => $amount,
            'site' => 'NEW',
          ],
        ]);

        if ($type != 'PSE') {
          $this->segment->track([
            'event' => 'TBO - Franquicia Multi',
            'userId' => $tigoId,
            'properties' => [
              'category' => 'Pasarela de Pago',
              'label' => $response->franchise . ' - ' . $_SESSION['environment'],
              'value' => (int) $response->amount,
              'site' => 'NEW',
            ],
          ]);
        }

        if ($response->state == 'success') {
          $status_response = TRUE;
          //Inválidamos la caché para el cliente específico.
          $company = $_SESSION['company'];
          BaseApiCache::invalidateTags([
            'findCustomerAccountsByIdentification:' . $company['nit'],
          ]);
          drupal_set_message(t("FACTURAS PAGADAS. </br>  Sus facturas han sido pagadas con éxito"));
        }
      }
    }
    catch (\Exception $e) {
      $this->segment->track([
        'event' => 'TBO - Retorno pasarela Multi - Tx',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Pasarela de Pago',
          'label' => 'Cancelado - ' . count($_SESSION['multiple_payment']['data']) . ' - ' . $_SESSION['environment'],
          'value' => $amount,
          'site' => 'NEW',
        ],
      ]);

      // drupal_set_message(t("Su pago no se ha completado. </br> Algo ha salido mal y no se ha podido completar el pago de sus facturas, por favor intente nuevamente más tarde"), 'error');.
      UtilMessage::getMessage($e);
    }

    // preparación del archivo excel.
    $type = 'xlsx';
    $data_export = $_SESSION['multiple_payment']['data'];
    $data_headers = [
      'Referencia de pago múltiple',
      'Contrato',
      'Referente de pago',
      'Periodo facturado',
      'Fecha de pago',
      'Valor de la factura',
      'Dirección',
      'Estado',
    ];

    $dir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    $date = date('Y-m-d H:i:s');
    $file_name = 'multiples-facturas-' . $date . '.xlsx';
    $path = $dir . '/' . $file_name;
    $header = '';

    $writer = WriterFactory::create(Type::XLSX);
    $writer->openToFile($path);

    $header = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    $writer->getCurrentSheet()->setName('Pago de Múltiples facturas');

    // Preparación de facturas.
    $group_invoices = [];

    // Maximo numero de filas.
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_billing');
    $max_rows = intval($tempstore->get('max_rows'));

    foreach ($_SESSION['multiple_payment']['data'] as $item) {
      if (count($group_invoices) < $max_rows) {
        array_push($group_invoices, [
          isset($multiple__payment_reference) ? $multiple__payment_reference : 'No disponible',
          $item['contract'],
          $item['payment_reference'],
          $item['period'],
          $item['date_payment'],
          $item['invoice_value2'],
          $item['address'],
          $status_response ? 'Exitosa' : 'Fallida',
        ]);
      }
    }

    $writer->addRow($data_headers);
    foreach ($group_invoices as $item) {
      $writer->addRow($item);
    }

    if ($writer->close()) {
    }
    else {
    }

    // Envio de correo.
    $name = '';
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }

    $mail = $account_fields->getEmail();

    $tokens = [
      'date' => date(),
      'user' => $name,
      'name' => $_SESSION['company']['name'],
      'enterprise' => $_SESSION['company']['name'],
      'enterprise_num' => $_SESSION['company']['nit'],
      'enterprise_doc' => $_SESSION['company']['docType'],
      'admin_enterprise' => $name,
      'admin_mail' => $mail,
      'status' => $status_response ? t('exitosa') : t('fallida'),
      'link' => $GLOBALS['base_url'],
      'attachments' => [
        'filepath' => $path,
        'filename' => $file_name,
        'filemime' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
      ],
    ];

    // Obtener todos los admin empresa para enviar correo.
    $admins = $this->repository->getAlladminCompany($_SESSION['company']['id']);

    foreach ($admins as $admin) {
      $tokens['user'] = isset($admin->full_name) ? $admin->full_name : $admin->name;
      $tokens['mail_to_send'] = $admin->mail;
      $send = $this->service_message->send_message($tokens, 'multiple_invoices_payment');
    }
    unlink($path);

    // Log auditoria.
    $this->saveAuditLog($_SESSION['multiple_payment']['data'], 'fija', $status_response);

    if (!$status_response) {
      $_SESSION['block_info']['type'] = 'DECLINED';
      $_SESSION['block_info']['url'] = 'reintentar';
      $_SESSION['block_info']['action'] = 1;
      $_SESSION['block_info']['render'] = $this->createBlock();
    }
    else {
      unset($_SESSION['multiple_payment']);
    }

    $_SESSION['data_cache'] = $_SESSION['multiple_payment'];

    return new RedirectResponse(Url::fromUri('internal:/factura-actual')
      ->toString());
  }

  /**
   *
   */
  public function saveAuditLog($invoices, $type, $status) {

    foreach ($invoices as $invoice) {
      $name = $this->service_log->loadName();
      $details = [];

      if ($status) {
        $details = t('El usuario  @name pagó correctamente la factura @type con número @invoice asociada al contrato @contract',
          ['@name' => $name, '@type' => $type, '@invoice' => $invoice['invoiceId'], '@contract' => $invoice['contract']]);
      }
      else {
        $details = t('La factura @type con número @invoice asociada al contrato @contract no pudo pagarse exitosamente',
          ['@name' => $name, '@type' => $type, '@invoice' => $invoice['invoiceId'], '@contract' => $invoice['contract']]);
      }

      $data = [
        'companyName' => $_SESSION['company']['name'],
        'companyDocument' => $_SESSION['company']['nit'],
        'event_type' => t('Facturación'),
        'description' => t('Usuario realizó pago de mútiples facturas @type', ['@type' => $type]),
        'details' => $details,
        'old_value' => 'No disponible',
        'new_value' => 'No disponible',
      ];

      $this->service_log->insertGenericLog($data);
    }
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

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account) {

    $user = \Drupal::currentUser()->getRoles(TRUE);
    $roles_autorized = [
      'admin_company',
      'admin_company',
      'admin_grupo',
      'admin_group',
      'super_admin',
      'administrator',
    ];
    foreach ($roles_autorized as $rol) {
      if (!in_array($rol, $user)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
