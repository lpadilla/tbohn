<?php

namespace Drupal\tbo_billing\Services;

use Drupal\user\Entity\User;

/**
 * Class BillingDeliveryService.
 *
 * @package Drupal\tbo_billing
 */
class BillingDeliveryService implements BillingDeliveryInterface {
  protected $name;
  protected $uid;
  protected $role;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->uid = \Drupal::currentUser()->id();
    $account = User::load($this->uid);

    // Load fields account.
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $this->name = $account_fields->full_name;
    }
    else {
      $this->name = \Drupal::currentUser()->getAccountName();
    }

    // Get name rol.
    $this->role = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);
  }

  /**
   * @param $data
   */
  public function saveAuditLog($data) {
    // Se guarda el log de auditoria
    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => $data['event_type'],
      'description' => $data['description'],
      'details' => $data['details'],
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    \Drupal::service('tbo_core.audit_log_service')->insertGenericLog($data);
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

}
