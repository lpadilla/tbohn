<?php

namespace Drupal\tbo_permissions\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ConsultCompaniesBlockedCardRestService.
 *
 * @package Drupal\tbo_permissions\Services\Rest
 */
class ConsultCompaniesBlockedCardRestService {

  private $currentUser;

  private $auditLogService;

  /**
   * ConsultCompaniesBlockedCardRestService constructor.
   */
  public function __construct() {
    $this->auditLogService = \Drupal::service('tbo_core.audit_log_service');
    $this->auditLogService->loadName();
  }

  /**
   * Responds to GET requests.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
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

    // Save access event audit log.
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
        'description' => t('Usuario accede a listado de empresas con funcionalidad bloqueada'),
        'details' => t('Usuario @user accede a listado de empresas con funcionalidad bloqueda', $tokenLog),
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
      ];
      $this->auditLogService->insertGenericLog($dataLog);

      return new ResourceResponse([TRUE]);
    }

    // Here we can validate the selected company.
    if (isset($_GET['validate_document_number']) && isset($_GET['validate_document_type'])) {
      try {
        $data = $permissionsRepository->validateCompanyDocument($_GET['validate_document_type'], $_GET['validate_document_number']);
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
      $data = $permissionsRepository->getCompaniesWithBlockedCards($filters);

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
   *   Current user.
   * @param array $data
   *   Data params.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Resource Response.
   */
  public function post(AccountProxyInterface $currentUser, array $data) {
    $this->currentUser = $currentUser;

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access list company')) {
      throw new AccessDeniedHttpException();
    }

    // We get repository, so we can get the records to build the excel report.
    $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');
    $companiesBlockedCards = $permissionsRepository->getAllCompaniesWithBlockedCards();
    if (count($companiesBlockedCards) > 0) {
      // Prepare file path.
      $dir = \Drupal::service('stream_wrapper_manager')
        ->getViaUri('public://')
        ->realpath();

      $doc_name = "reporte-empresas-cards-bloqueados-";

      $date = date('Y-m-d');
      $file_name = $doc_name . $date . '.xlsx';
      $path = $dir . '/' . $file_name;

      try {
        // Prepare the Excel file.
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($path);

        $writer->getCurrentSheet()
          ->setName('Empresas con Cards bloqueados');

        // Prepare the rows.
        $writer->addRow($data['headers']);
        foreach ($companiesBlockedCards as $row) {
          $writer->addRow($row);
        }

        $writer->close();

        $file_data = [
          'file_name' => $file_name,
        ];

        // Save audit log on export to Excel format.
        $tokenLog = [
          '@user' => $this->auditLogService->getName(),
        ];
        $dataLog = [
          'companyName' => t('No aplica'),
          'companyDocument' => t('No aplica'),
          'companySegment' => t('No aplica'),
          'event_type' => t('Cuenta'),
          'description' => t('Usuario exporta lista negra de empresas'),
          'details' => t('Usuario @user exporta archivo Excel con información de lista negra de empresas con funcionalidades bloqueadas', $tokenLog),
          'old_value' => t('No disponible'),
          'new_value' => t('No disponible'),
        ];
        $this->auditLogService->insertGenericLog($dataLog);

        return new ResourceResponse($file_data);
      }
      catch (\Exception $e) {
        return new ResourceResponse(['error' => $e->getMessage()]);
      }
    }

    return new ResourceResponse(['error' => t('No se encontraron empresas con cards bloqueados.')]);
  }

}
