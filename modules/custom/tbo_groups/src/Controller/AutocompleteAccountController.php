<?php

namespace Drupal\tbo_groups\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AutocompleteAccountController.
 *
 * @package Drupal\tbo_groups\Controller
 */
class AutocompleteAccountController extends ControllerBase {

  /**
   * Autocomplete email.
   *
   * @return string
   *   Return Hello string.
   */
  public function autocompleteAccounts($account) {

    $suggestedAccount = [];

    $clientType = $_SESSION['company']['docType'];
    $clientId = $_SESSION['company'][$clientType];
    $environment = $_SESSION['company']['environment'];

    if ($clientId == 0 || $clientId == NULL) {
      return $suggestedAccount;
    }

    if ($environment == 'movil') {
      $suggestedAccount_ids = \Drupal::service('tbo_api.client');
      $endDate = date('d/m/Y', time());

      $params['query'] = [
        'countInvoiceToReturn' => 6,
        'endDate' => $endDate,
        'type' => 'mobile',
      ];
      $params['tokens'] = [
        'clientId' => $clientId,
      ];

      $response = $suggestedAccount_ids->getBillingInformation($params);
      if ($response && $account) {
        foreach ($response as $a) {
          if (strpos((string) ($a['number']), $account) !== FALSE) {
            array_push($suggestedAccount, ['account' => $a['number']]);
          }
        }
      }
    }

    return new JsonResponse($suggestedAccount);
  }

  /**
   *
   */
  public function getAllAccounts() {

    $suggestedAccount = [];

    $clientType = $_SESSION['company']['docType'];
    $clientId = $_SESSION['company'][$clientType];
    $environment = $_SESSION['company']['environment'];

    if ($clientId == 0 || $clientId == NULL) {
      return $suggestedAccount;
    }

    if ($environment == 'movil') {
      $suggestedAccount_ids = \Drupal::service('tbo_api.client');
      $endDate = date('d/m/Y', time());

      $params['query'] = [
        'countInvoiceToReturn' => 6,
        'endDate' => $endDate,
        'type' => 'mobile',
      ];
      $params['tokens'] = [
        'clientId' => $clientId,
      ];

      $response = $suggestedAccount_ids->getBillingInformation($params);
      if ($response) {
        foreach ($response as $a) {
          array_push($suggestedAccount, ['account' => $a['number']]);
        }
      }
    }

    return new JsonResponse($suggestedAccount);
  }

}
