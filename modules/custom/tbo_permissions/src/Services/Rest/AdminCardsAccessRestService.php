<?php

namespace Drupal\tbo_permissions\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AdminCardsAccessRestService.
 *
 * @package Drupal\tbo_permissions\Services\Rest
 */
class AdminCardsAccessRestService {

  private $currentUser;

  private $auditLogService;

  /**
   * AdminCardsAccessRestService constructor.
   */
  public function __construct() {
    $this->auditLogService = \Drupal::service('tbo_core.audit_log_service');
    $this->auditLogService->loadName();
  }

  /**
   * Responds to GET requests.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Resource Response.
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    $data2 = [];

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access list company')) {
      throw new AccessDeniedHttpException();
    }

    // Get repository.
    $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');

    $request = $_GET;
    if (isset($_GET['q'])) {
      $q = $_GET['q'];
    }

    if (isset($_GET['save_audit_log'])) {
      // Save audit log on consult.
      $tokenLog = [
        '@user' => $this->auditLogService->getName(),
      ];
      $dataLog = [
        'companyName' => t('No aplica'),
        'companyDocument' => t('No aplica'),
        'companySegment' => t('No aplica'),
        'event_type' => t('Cuenta'),
        'description' => t('Usuario accede a bloqueo de funcionalidades por empresa'),
        'details' => t('Usuario @user accede a bloqueo de funcionalidades por empresa', $tokenLog),
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
      ];
      $this->auditLogService->insertGenericLog($dataLog);

      return new ResourceResponse([TRUE]);
    }

    // Here we can validate the selected company.
    if (isset($_GET['document_number']) && isset($_GET['document_type'])) {
      try {
        $data = $permissionsRepository->validateCompanyDocument($_GET['document_type'], $_GET['document_number']);
      }
      catch (\Exception $e) {
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      return new ResourceResponse($data);
    }

    // Handle the autocomplete Card Name field.
    if (isset($_GET['autocomplete_card_name'])) {
      if ($_GET['autocomplete_card_name']) {
        try {
          $data = $permissionsRepository->getAutocompleteCards($_GET['autocomplete_card_name']);
        }
        catch (\Exception $e) {
          return new ResourceResponse(UtilMessage::getMessage($e));
        }
        foreach ($data as $key => $content) {
          array_push($data2, (array) $content);
        }
        return new ResourceResponse($data2);
      }
    }

    $filters = $request;

    // Remove the unnecesary indexes from the filters.
    unset($filters['_format']);
    unset($filters['config_columns']);
    unset($filters['config_name']);
    if (isset($q)) {
      unset($filters['q']);
    }

    try {
      $data = $permissionsRepository->getAllCardsAccess($filters);
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    foreach ($data as $key => $content) {
      array_push($data2, (array) $content);
    }

    return new ResourceResponse($data2);
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user object.
   * @param array $data
   *   Parameters data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Resource Response.
   */
  public function post(AccountProxyInterface $currentUser, array $data) {
    $this->currentUser = $currentUser;
    $result = [];

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access list company')) {
      throw new AccessDeniedHttpException();
    }

    // Get repository.
    $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');

    // Now we update the Cards Access statuses.
    if (isset($data['cards_access_info'])) {
      $result = $permissionsRepository->updateCardsAccess($data['cards_access_info']);
    }

    return new ResourceResponse($result);
  }

}
