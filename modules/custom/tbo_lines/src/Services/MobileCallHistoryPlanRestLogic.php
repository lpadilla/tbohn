<?php

namespace Drupal\tbo_lines\Services;


use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MobileCallHistoryPlanRestLogic {

  protected $api;

  protected $account;

  /**
   * MobileCallHistoryRestLogic constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboApiClientInterface $api, AccountInterface $account) {
    $this->api = $api;
    $this->account = $account;
  }

  /**
   * @param AccountProxyInterface $currentUser
   *
   * @return ResourceResponse
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->account->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $prefix_contry = \Drupal::config('adf_rest_api.settings')
      ->get('prefix_country');
    $params = [
      'tokens' => [
        'msisdn' => $prefix_contry . $_SESSION['serviceDetail']['address'],
      ],
      'query' => [
        'grouped' => '1',
      ],
    ];

    $typeService = [
      'Minutos a Tigo' => 'mtigo',
      'Minutos a Todo Destino' => 'mdestino',
      'Minutos Larga Distancia Internacional' => 'mdistancia',
      'Minutos a Favoritos Tigo' => 'mfavorito',
      'Minutos Roaming' => 'mroaming',
      'Minutos a Otros Operadores' => 'operador',
    ];
    $data['mdestino'] = "0 MIN";
    $data['mdistancia'] = "0 MIN";
    $data['mfavorito'] = "0 MIN";
    $data['mtigo'] = "0 MIN";
    $data['mroaming'] = "0 MIN";
    $data['operador'] = "0 MIN";
    try {
      $response = $this->api->tolGetBalances($params);
      foreach ($response->balances as $key => $value) {
        if ($value->category == 'VOICE') {
          if ($typeService[$value->wallet]) {
              $plan = round($value->balanceAmount);
            $data[$typeService[$value->wallet]] = $plan . " MIN";
          }
        }
      }
    } catch (\Exception $e) {
      return new ResourceResponse($data);
    }

    return new ResourceResponse($data);
  }

}