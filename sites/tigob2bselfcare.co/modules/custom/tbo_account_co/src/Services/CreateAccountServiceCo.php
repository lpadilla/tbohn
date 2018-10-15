<?php

namespace Drupal\tbo_account_co\Services;

use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_account\Services\CreateAccountService;

/**
 * Class CreateAccountService.
 *
 * @package Drupal\tbo_account
 */
class CreateAccountServiceCo extends CreateAccountService {

  /**
   *
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    parent::__construct($tbo_config, $api);
  }

  /**
   * @param $documentType
   * @param $clientId
   * @param $contractId
   * @param $referentPayment
   * @param $form_id
   * @return bool
   */
  public function validateFormServiceMobile($documentType, $clientId, $contractId, $referentPayment, $form_id) {
    // Get service mobile.
    $response_mobile = $this->getDataMobile($documentType, $clientId);
    $contracts_references = [];

    $for_data = $response_mobile->billingAccountCollection->billingAccount;

    if (is_object($for_data)) {
      $aux = [];
      array_push($aux, $response_mobile->billingAccountCollection->billingAccount->invoiceCollection->invoice);
      $for_data = $aux;
    }
    else {
      $aux = [];
      foreach ($for_data as $item) {
        if (is_object($item->invoiceCollection->invoice)) {
          array_push($aux, $item->invoiceCollection->invoice);
        }
        else {
          foreach ($item->invoiceCollection->invoice as $value) {
            array_push($aux, $item->invoiceCollection->invoice);
          }
        }
      }
      $for_data = $aux;
    }

    if ($response_mobile) {
      $this->name_company = $response_mobile->clientName;

      foreach ($for_data as $invoice) {
        if (is_object($invoice)) {
          $contracts_references[$invoice->contract][rand()] = $this->formatMobileSerial($invoice->invoiceNumber);
        }
        else {
          foreach ($invoice as $detail) {
            $contracts_references[$detail->contract][rand()] = $this->formatMobileSerial($detail->invoiceNumber);;
          }
        }
      };

      if (!array_key_exists($contractId, $contracts_references)) {
        $message_error['contract_number'] = t('El número de contrato no es válido.');
      }
      else {
        if (in_array($referentPayment, $contracts_references[$contractId])) {
          $this->name_company = $response_mobile->contactName;
          if ($this->getCustomerByContractId($contractId)) {
            $this->fixed = 'fixed';
          }
          $this->mobile = 'mobile';
        }
        else {
          $message_error['referent_payment'] = t('El referente de pago no es válido.');
        }
      }

      $aux_references = implode(',', call_user_func_array('array_merge', $contracts_references));

      if (strpos($aux_references, $referentPayment) === FALSE) {
        $message_error['referent_payment'] = t('El referente de pago no es válido.');
      }
    }
    else {
      $message_error['document_number'] = t('El tipo o número de documento no son válidos.');
    }

    // If validation errors, save them to the hidden form field in JSON format.
    if ($message_error) {
      // Validamos la cantidad de intentos fallidos.
      $this->checkLimitSubmit($form_id, TRUE);
    }
    return $message_error;
  }

  /**
   *
   */
  public function formatMobileSerial($serial) {
    $index = strpos($serial, '-');
    $part_1 = substr($serial, 0, $index + 1);
    $part_2 = substr($serial, $index + 2, strlen($serial));
    return $part_1 . $part_2;
  }

}
