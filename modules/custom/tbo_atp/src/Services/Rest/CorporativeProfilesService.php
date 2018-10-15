<?php

namespace Drupal\tbo_atp\Services\Rest;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;

/**
 * Class CorporativeProfilesService.
 *
 * @package Drupal\tbo_atp\Services\Rest
 */
class CorporativeProfilesService {

  protected $api;
  protected $tbo_config;
  protected $service;

  /**
   * ServicePortfolioService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->service = \Drupal::service('tbo_core.audit_log_service');
  }

  /**
   * @param AccountProxyInterface $currentUser
   * @return ResourceResponse - Contracts, accountId and enterprise name
   */
  public function get(AccountProxyInterface $currentUser) {

    // Denied cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // validate user permission.
    if(!$currentUser->hasPermission('access content')) {
      return new AccessDeniedHttpException();
    }

    $account = User::load($currentUser->id());
    $full_name = $account->get('full_name');
    $user = (empty($full_name) || isset($full_name)) ? $currentUser->getAccountName() : $full_name;

    if(!isset($_GET['log'])) {
      $params['tokens']['accountId'] = $_GET['accountId'];
      $lines = 0;

      try {
        $response_atp = $this->api->getATPAccountProfilesByAccountId($params);
        foreach ($response_atp->profileCollection as $key => $value) {

          $params_atp_d['tokens']['profile_id'] = $response_atp->profileCollection[$key]->id;

          try {
            $response_atp_d = $this->api->getATPAccountProfileDetailsByProfileId($params_atp_d);
          } catch(\Exception $e) {
          }

          $serviceConfig = \Drupal::service('tbo_general.tbo_config');

          $lines += $response_atp_d->linesAmount;

          $response['data'][] = [
            'profile' =>  ucwords(strtolower($value->name)),
            'profile_description' => $value->description,
            'associated_lines' => (empty($response_atp_d->linesAmount)) ? t('No disponible') : $response_atp_d->linesAmount,
            'package_value_show' => $serviceConfig->formatCurrency($response_atp_d->value),
            'package_value' => $response_atp_d->value,
            'total_value_show' => $serviceConfig->formatCurrency($response_atp_d->totalValue),
            'total_value' =>  $response_atp_d->totalValue,
            'lines' => '',
            'profile_id' => $value->id,
          ];

        }
        $response['total_lines'] = $lines;

        $token_log = [
          '@user' => $user,
          '@contract' => $_GET['contractId'],
        ];

        $data_log = [
          'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
          'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
          'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
          'event_type' => t('ATP'),
          'description' => t('Usuario consulto perfiles de sus planes corporativos (ATP) de sus servicios móviles'),
          'details' => t('Usuario @user consulto los perfiles de los planes corporativos (ATP) de su empresa del contrato @contract', $token_log),
          'old_value' => t('No disponible'),
          'new_value' => t('No disponible'),
        ];

        $this->service->insertGenericLog($data_log);
      } catch(\Exception $e) {
        //return new ResourceResponse(UtilMessage::getMessage($e));
        $response_atp = new \stdClass();
        $response_atp->profileCollection = [];
        $response['total_lines'] = 0;
        $response['data'] = [];
      }
    }
    else {

      $token_log = [
        '@user' => $user,
        '@profile' =>  $_GET['profile'],
        '@contract' => $_GET['contract'],
      ];

      if($_GET['type'] == 'lines') {
        $description = t('Usuario consulto detalle de las líneas asociadas a su perfil');
        $detail = t('Usuario @user consulto el detalle de las líneas asociadas al perfil @profile del contrato @contract de los planes corporativos (ATP) de su empresa', $token_log);
      }
      else {
        $description = t('usuario consulto perfil especifico de los planes corporativos (ATP) de sus servicios móviles');
        $detail = t('usuario @user consulto el perfil  @profile  del contrato @contract de los planes corporativos (ATP) de su empresa', $token_log);
      }

      // Save audit log.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('ATP'),
        'description' => $description,
        'details' => $detail,
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
      ];
      $this->service->insertGenericLog($data_log);
      $response = 'OK';
    }

    return new ResourceResponse($response);
  }
}
