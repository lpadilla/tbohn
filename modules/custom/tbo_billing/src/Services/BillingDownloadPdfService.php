<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_entities\Entity\CompanyEntity;

/**
 * Class BillingDownloadPdfService.
 *
 * @package Drupal\tbo_billing
 */
class BillingDownloadPdfService implements BillingDownloadPdfInterface {

  private $tboApiClient;

  /**
   * Constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $tboApiClient
   *   TBO API client.
   */
  public function __construct(TboApiClientInterface $tboApiClient) {
    $this->tboApiClient = $tboApiClient;
  }

  /**
   * Get the PDF invoice.
   *
   * @param string $contractNumber
   *   Contract number needed to get the invoice.
   * @param string $type
   *   Type of service.
   * @param string $invoiceNumber
   *   Invoice number.
   *
   * @return array
   *   Array with the URL for downloading the PDF invoice.
   */
  public function getInvoicePdf($contractNumber, $type, $invoiceNumber) {
    $errorMessage = t('La factura no se encuentra disponible en formato electrónico.');
    $result = FALSE;
    $downloadUrl = FALSE;

    if (isset($contractNumber)) {
      if ($type == 'movil' && isset($invoiceNumber)) {
        // Mobile.
        $param_send = [
          'query' => [
            'contractNumber' => $contractNumber,
            'type' => 'mobile',
            'invoiceSerial' => 'B1',
            'invoiceRequestedType' => 'Detail',
          ],
          'tokens' => [
            'invoiceNumber' => $invoiceNumber,
          ],
        ];

        try {
          $result = $this->tboApiClient->getInvoicePDFMobile($param_send);
        }
        catch (\Exception $e) {
          $mensaje = UtilMessage::getMessage($e);

          // Return message in rest.
          drupal_set_message($mensaje['message'], 'error');
        }

        if (isset($result->url)) {
          $downloadUrl = $result->url;
        }
        else {
          $downloadUrl = $result;
        }
      }
      elseif ($type == 'fijo' && isset($invoiceNumber)) {
        // Landline.
        $param_send = [
          'query' => [],
          'tokens' => [
            'contractNumber' => $contractNumber,
            'invoiceNumber' => $invoiceNumber,
          ],
        ];

        try {
          $result = $this->tboApiClient->getSpoolUrlByContractIdAndInvoiceId($param_send);
        }
        catch (\Exception $e) {
          $mensaje = UtilMessage::getMessage($e);

          // Return message in rest.
          drupal_set_message($mensaje['message'], 'error');
        }

        if (isset($result->url)) {
          $downloadUrl = $result->url;
        }
      }
      else {
        $errorMessage = t('No es posible usar el servicio. Faltan parámetros.');
      }
    }

    $register = $this->saveDownloadPdfAuditLog($type, $invoiceNumber, $contractNumber);

    return [
      'url' => $downloadUrl,
      'errormsg' => $errorMessage,
    ];
  }

  /**
   * Save an audit log for the PDF download.
   *
   * @param string $type
   *   Indicates if is Mobile or Landline.
   * @param string $invoiceNumber
   *   Invoice number.
   * @param string $contractNumber
   *   Contract number.
   *
   * @return bool
   *   Result of the audit log save.
   */
  public function saveDownloadPdfAuditLog($type, $invoiceNumber, $contractNumber) {
    // Get user info log activity.
    $current_user = \Drupal::currentUser();
    $user_names = $current_user->getAccountName();
    $uid = $current_user->id();
    $logTokens = [
      '@username' => $user_names,
      '@invoiceNumber' => $invoiceNumber,
      '@contractNumber' => $contractNumber,
    ];
    $message = t('Usuario @username realizó descarga de la factura @invoiceNumber del contrato @contractNumber', $logTokens);

    $query = \Drupal::service('entity.query')
      ->get('company_user_relations')
      ->condition('users', $uid);
    $entity_ids = $query->execute();
    foreach ($entity_ids as $key => $value) {
      $entity_id = $value;
    }

    $companyName = 'No associated company';
    $companyDocument = 'NA';
    $companySegment = 'NA';

    if (isset($entity_id)) {

      $company_id = $_SESSION['company']['id'];
      if (isset($company_id)) {
        $company = CompanyEntity::load($company_id);
      }

      if (isset($company)) {
        $companyName = $company->getCompanyName();
        $companyDocument = $company->getDocumentNumber();
        $companySegment = $company->getCompanySegment();
      }
    }

    $values = [
      'user_names' => $user_names,
      'company_name' => $companyName,
      'date' => \Drupal::service('date.formatter')
        ->format(time(), 'custom', 'Y-m-d h:m a'),
      'company_document_number' => $companyDocument,
      'company_segment' => $companySegment,
      'user_role' => $current_user->getRoles(TRUE),
      'category' => t('Facturación'),
      'description' => t('Usuario descarga factura en PDF'),
      'event_type' => '',
      'details' => $message,
      'old_values' => '',
      'new_values' => '',
    ];

    $audit_log_service = \Drupal::service('tbo_core.audit_log_service');
    $logged = $audit_log_service->setAuditLog($values);
    if ($logged != NULL) {
      return TRUE;
    }

    return FALSE;
  }

}
