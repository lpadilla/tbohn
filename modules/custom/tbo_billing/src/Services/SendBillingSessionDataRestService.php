<?php
namespace Drupal\tbo_billing\Services;

use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\user\Entity\User;

/**
 * Class SendBillingSessionDataRestService.
 *
 * @package Drupal\tbo_billing\Services
 */
class SendBillingSessionDataRestService {
  /**
   * Get response.
   */
  public function get($decode) {
    if (array_key_exists('service_detail', $decode)) {
      $_SESSION['serviceDetail'] = [
        'contractId' => $decode['contractId'],
        'address' => $decode['address'],
        'category' => $decode['category'],
        'status' => $decode['status'],
        'plan' => $decode['plan'],
        'productId' => $decode['productId'],
        'subscriptionNumber' => $decode['subscriptionNumber'],
        'serviceType' => $decode['serviceType'],
        'measuringElement' => $decode['measuringElement'],
      ];
    }
    else {
      $_SESSION['sendDetail'] = [
        'contractId' => $decode['contractId'],
        'docNumber' => $decode['docNumber'],
        'showDetails' => TRUE,
        'paymentReference' => $decode['paymentReference'],
        'address' => $decode['address'],
        'city' => $decode['city'],
        'line' => $decode['line'],
        'invoiceId' => $decode['invoiceId'],
        'state' => $decode['state'],
        'country' => $decode['country'],
        'zipcode' => $decode['zipcode'],
      ];
    }
    // Save audit log.
    $this->saveAuditLog();
    return 'OK';
  }

  /**
   * Save audit log.
   */
  public function saveAuditLog() {
    $log = AuditLogEntity::create();
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }
    // Get name rol.
    $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);
    $factura = isset($_SESSION['sendDetail']['paymentReference']) ? $_SESSION['sendDetail']['paymentReference'] : '';
    $log->set('created', time());
    $log->set('user_id', $uid);
    $log->set('user_names', $name);
    $log->set('company_document_number', isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '');
    $log->set('company_name', isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '');
    $log->set('user_role', $rol);
    $log->set('event_type', 'Facturación');
    $log->set('description', 'Consulta detalle de factura ' . $_SESSION['company']['environment']);
    $log->set('details', 'Usuario ' . $name . ' consulto detalle de la factura ' . $factura . ' de los servicios ' . $_SESSION['company']['environment']);
    $log->save();
  }
}