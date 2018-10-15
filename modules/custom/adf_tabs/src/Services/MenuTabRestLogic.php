<?php

namespace Drupal\adf_tabs\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;

/**
 *
 */
class MenuTabRestLogic {

  protected $currentUser;

  /**
   * MenuTabRestLogic constructor.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Insert log.
   *
   * @param $data
   *   option selected
   *
   * @return $this
   */
  public function post($data) {
    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $option = $data['option'];
    $category = $_SESSION['serviceDetail']['category'];
    $productId = $_SESSION['serviceDetail']['productId'];
    $entityId = \Drupal::entityQuery('category_services_entity')->condition('parameter', $productId)->execute();
    $keyId = array_keys($entityId);
    $entityCategory = \Drupal::entityTypeManager()
      ->getStorage('category_services_entity')
      ->load($keyId[0]);
    $type = $entityCategory->get('type_category');
    $service = \Drupal::service('tbo_core.audit_log_service');
    $userName = $this->currentUser->getAccount()->full_name;
    $details = t('Usuario @userName solicita transacción @option de la categoría @category @type', ['@userName' => $userName, '@option' => $option, '@category' => $category, '@type' => $type]);
    $eventType = t('Servicios');
    $description = t("Usuario solicita @option", ['@option' => $option]);
    $params = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'companySegment' => $_SESSION['company']['segment'],
      'event_type' => $eventType,
      'description' => $description,
      'details' => $details ,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    $service->insertGenericLog($params);
    return (new ResourceResponse('Log insertado'))->addCacheableDependency($build);

  }

}
