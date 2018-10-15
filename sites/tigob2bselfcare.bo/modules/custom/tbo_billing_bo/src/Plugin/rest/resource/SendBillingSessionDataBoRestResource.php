<?php

namespace Drupal\tbo_billing_bo\Plugin\rest\resource;

use Drupal\tbo_billing\Plugin\rest\resource\SendBillingSessionDataRestResource;
use Drupal\user\Entity\User;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "send_billing_session_data_bo_rest_resource",
 *   label = @Translation("Send billing session data rest resource BO"),
 *   uri_paths = {
 *     "canonical" = "/billing/session/data/bo"
 *   }
 * )
 */
class SendBillingSessionDataBoRestResource extends SendBillingSessionDataRestResource {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }


    $decode = json_decode($_GET['data'], TRUE);
    
    
    if(array_key_exists('service_detail', $decode)) {
      $_SESSION['serviceDetail'] = [
        'contractId' => $decode['contractId'],
        'address' => $decode['address'],
        'category' => $decode['category'],
        'status' => $decode['status'],
        'plan' => $decode['plan'],
        'productId' => $decode['productId'],
        'subscriptionNumber' => $decode['subscriptionNumber'],
        'serviceType' => $decode['serviceType'],
        "invoice" => ["msisdn" => $decode['msisdn']],
        'client_code_for_detail'=> $decode['client_code'],
        
			];
		} else{
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
        'zipcode'=> $decode['zipcode'],
        "invoice" => ["msisdn" => $decode['msisdn']],
        'client_code_for_detail'=> $decode['client_code'],
			];
		}
    //Save audit log
    $this->saveAuditLog();
   
    return new ResourceResponse('OK');
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
