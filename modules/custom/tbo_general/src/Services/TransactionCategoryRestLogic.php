<?php

namespace Drupal\tbo_general\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;

/**
 *
 */
class TransactionCategoryRestLogic {

  protected $currentUser;

  /**
   * TransactionCategoryRestLogic constructor.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {

    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $option = $_POST['option'];
    $category = $_SESSION['serviceDetail']['category'];
    $entityId = \Drupal::entityQuery('category_services_entity')->condition('label', $category)->execute();
    $keyId = array_keys($entityId);
    $entityCategory = \Drupal::entityTypeManager()
      ->getStorage('category_services_entity')
      ->load($keyId[0]);
    $type = $entityCategory->get('type_category');
    $service = \Drupal::service('tbo_core.audit_log_service');
    $userName = $this->currentUser->getAccount()->full_name;
    $params = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'companySegment' => $_SESSION['company']['segment'],
      'event_type' => 'Servicios',
      'description' => "Usuario solicita $option",
      'details' => "Usuario $userName solicita transacción $option de la categoría $category $type",
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    $service->insertGenericLog($params);
    return (new ResourceResponse('Log insertado'))->addCacheableDependency($build);
  }

}
