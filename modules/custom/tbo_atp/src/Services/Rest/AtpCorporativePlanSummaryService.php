<?php

namespace Drupal\tbo_atp\Services\Rest;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class AtpCorporativePlanSummaryService.
 *
 * @package Drupal\tbo_atp\Services\Rest
 */
class AtpCorporativePlanSummaryService {

  protected $api;
  private $tbo_config;
  private $currentUser;

  /**
   * AtpCorporativePlanSummaryService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {

    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Validate user permission
    if (!$currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    if (!empty($_GET['log_details'])) {
      // Save audit log
      $this->saveAuditLogClickDetailsButton($_GET['contract']);
    }
    else {


      $accountId = '';
      if (!empty($_GET['accountId'])) {
        $accountId = $_GET['accountId'];
      }

      $params_profiles['tokens'] = [
        'accountId' => $accountId,
      ];

      try {
        // First, we get the Account's Profiles.
        $resultProfiles = $this->api->getATPAccountProfilesByAccountId($params_profiles);

        // Now we save an array of the Profiles with status "P".
        $filteredProfilesCollection = [];
        foreach ($resultProfiles->profileCollection as $key => $profile) {
          if (!empty($profile->billingAccount)) {
            if ($profile->status == 'P') {
              $filteredProfilesCollection[] = $profile;
            }
          }
        }

        // Next, we get each Profile details, so we can do the summations.
        $totalValuePlan = 0;
        $servicesAmountPlan = 0;
        $linesAmountPlan = 0;
        $flagUnavailable = true;
        foreach ($filteredProfilesCollection as $profile) {
          $params_profile_details['tokens'] = [
            'profile_id' => $profile->id,
          ];
          try {
            $resultProfileDetails = $this->api->getATPAccountProfileDetailsByProfileId($params_profile_details);

            // Double check Status.
            if ($resultProfileDetails->status == 'P') {
              // Summation of "totalValue" values.
              $totalValuePlan += (empty($resultProfileDetails->totalValue)) ? 0 : intval($resultProfileDetails->totalValue);

              // Summation of "servicesAmount" values.
              if (!empty($resultProfileDetails->servicesAmount)) {
                $servicesAmountPlan += $resultProfileDetails->servicesAmount;
                $flagUnavailable = false;
              }

              // Summation of "linesAmount" values.
              $linesAmountPlan += (empty($resultProfileDetails->linesAmount)) ? 0 : intval($resultProfileDetails->linesAmount);
            }
          } catch (\Exception $e) {}
        }

        $response['accountId'] = $accountId;

        // Format the total value with the stablished currency configuration.
        $serviceConfig = \Drupal::service('tbo_general.tbo_config');
        $totalValuePlanFormated = $serviceConfig->formatCurrency($totalValuePlan);
        $response['totalValuePlan'] = $totalValuePlanFormated;

        // In case none of the Profiles has a "servicesAmount" value, we print an Unavailable message.
        if ($flagUnavailable) {
          $response['servicesAmountPlan'] = t('No disponible');
        }
        else {
          $response['servicesAmountPlan'] = $servicesAmountPlan;
        }

        $response['linesAmountPlan'] = $linesAmountPlan;

        // We get the Billing Cycle.
        $cycle = ($resultProfiles->profileCollection[0]->cycle == 0) ? 1 : $resultProfiles->profileCollection[0]->cycle;
        if (!empty($cycle)) {
          $response['cycle'] = \Drupal::service('tbo_atp.general_service')->getFormattedBillingCycle($cycle, 'card');
        }
        else {
          $response['cycle'] = t('No disponible');
        }

        // We get the Minimum and Maximum Rank for the Account Plan.
        $params_account_details['tokens'] = [
          'accountId' => $accountId,
        ];
        try {
          $resultAccountDetails = $this->api->getATPAccountDetailsByAccountId($params_account_details);
          $response['minimumRank'] = (empty($resultAccountDetails->contractCollection[0]->rank->minimumRank)) ? t('No disponible') : $serviceConfig->formatCurrency($resultAccountDetails->contractCollection[0]->rank->minimumRank);
          $response['maximumRank'] = (empty($resultAccountDetails->contractCollection[0]->rank->maximumRank)) ? t('No disponible') : $serviceConfig->formatCurrency($resultAccountDetails->contractCollection[0]->rank->maximumRank);

          // Profiles count
          $response['profilesCount'] = count($filteredProfilesCollection);

          // Save audit log
          $this->saveAuditLog($resultAccountDetails->billingAccount);
        } catch (\Exception $e) {
          $response['minimumRank'] = t('No disponible');
          $response['maximumRank'] = t('No disponible');
          $response['profilesCount'] = 0;
        }
      }
      catch (\Exception $e) {
        $exception = UtilMessage::getMessage($e);
        //$exception['message_error'] = $exception['message_error'];
        $response['accountId'] = $accountId;
        $response['totalValuePlan'] = 0;
        $response['servicesAmountPlan'] = t('No disponible');
        $response['linesAmountPlan'] = 0;
        $response['cycle'] = t('No disponible');
        $response['minimumRank'] = t('No disponible');
        $response['maximumRank'] = t('No disponible');
        $response['profilesCount'] = 0;
        return new ResourceResponse($response);
      }
    }

    return new ResourceResponse($response);
  }

  /**
   * @param $name
   * @return string formated enterprise name
   */
  public function _enterpriseName($name) {
    $name = ucwords(strtolower($name));

    $words = [' SAS', ' LTDA', ' SA', ' SL'];
    foreach ($words as $word) {
      $name = str_ireplace($word, $word, $name);
    }

    return $name;
  }

  /**
   * Implements function to save audit log in Atp Corporative Plan Summary
   *
   * @param $contract
   */
  public function saveAuditLog($contract) {
    // Save audit log
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'ATP',
      'description' => t('Usuario consultó resumen de sus planes corporativos (ATP) de sus servicios móviles'),
      'details' => 'Usuario ' . $service->getName() . ' consulta resumen de planes corporativos (ATP) del contrato ' . $contract . ' de los servicios móviles de su empresa',
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log
    try {
      $service->insertGenericLog($data_log);
    }
    catch (\Exception $e) {
      // Validar la accion a tomar cuando se genere la excepcion.
      // SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction:.
    }

  }

  /**
   * Implements function to save audit log in Atp Corporative Plan Summary
   *
   * @param $contract
   */
  public function saveAuditLogClickDetailsButton($contract) {
    // Save audit log
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'ATP',
      'description' => t('Usuario accede al detalle de plan de ATP'),
      'details' => 'Usuario ' . $service->getName() . ' consultó el detalle del plan del contrato ' . $contract . ' de los planes corporativos (ATP) de su empresa',
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log
    $service->insertGenericLog($data_log);
  }
}
