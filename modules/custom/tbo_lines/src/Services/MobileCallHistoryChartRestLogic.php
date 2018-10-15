<?php

namespace Drupal\tbo_lines\Services;


use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MobileCallHistoryChartRestLogic {

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
    $filter_service = \Drupal::service('tbo_lines.call_history_filter_date');
    $service_date = $filter_service->getFilterDate();

    $end_date_request = \Drupal::request()->query->get('end_date');
    $init_date_request = \Drupal::request()->query->get('init_date');


    $dates['date_end'] = isset($end_date_request) ? $end_date_request : $service_date['end_date'];
    $dates['date_ini'] = isset($init_date_request) ? $init_date_request : $service_date['init_date'];

    $error_date = $filter_service->validateRangeDate($dates['date_ini'], $dates['date_end']);
    \Drupal::logger("Rango de error")->notice(print_r($error_date, TRUE));

    if ($error_date !== "") {
      drupal_set_message($error_date, 'error');
      return new ResourceResponse("Error en las fechas");
    }
    $params = [
      'query' => [
        'dateFrom' => $dates['date_ini'],
        'dateTill' => $dates['date_end'],
      ],
      'tokens' => [
        'account' => $_SESSION['serviceDetail']['address'],
      ],
      'headers' => [
        'sourceIp' => \Drupal::request()->server->get('SERVER_ADDR'),
      ],
    ];
    $get_type_format = \Drupal::config('tbo_general.settings')->get('region')['format_date'];
    $format = \Drupal::config('core.date_format.' . $get_type_format)->get('pattern');
    $dataChart = [];
    $typeSevice = [
      'ONNET' => 0,
      'n/a' => 1,
      'CUG' =>2,
      'LDI' => 3,
      'OFFNET' => 4,
      'Roaming' => 5
    ];
    $dateInit = new \DateTime($dates['date_ini']);
    $dateEnd = new \DateTime($dates['date_end']);
    $dataChart['labels'] = $this->getArrayLabels($dateEnd->diff($dateInit)->days, $dates['date_ini']);
    $dataChart['cantLabels']=$dateInit->diff($dateEnd)->days;
    $dataChart['data']=[];
    try {
      $detailList = $this->api->getCustomerCallsDetailsService($params);
      foreach ($detailList as $key => $value) {
        $get_date = new \DateTime($value->callDateTime);
        $posDay = $get_date->diff($dateInit)->days;
        $postService = $typeSevice[$value->callType];
        $dataChart['data'][$posDay]['values'][$postService] += $value->callDuration;
        $dataChart['data'][$posDay]['date'] = $get_date->format($format);
      }

    } catch (\Exception $e) {
      return new ResourceResponse($dataChart);
    }

    return new ResourceResponse($dataChart);
  }

  /**
   * Get labels
   *
   * @param $range
   * @param $date
   *
   * @return array
   */
  private function getArrayLabels($range,$date_init) {
    $date=new \DateTime($date_init);
    $labels = [];
    for ($i = 0; $i <= $range; $i++) {
      if ($i > 0) {
        $date->add(new \DateInterval('P1D'));
      }
      $labels[] = ltrim($date->format('d'), "0");
      //$labels[] = $date->format('d-m-Y');
    }
    return $labels;
  }
}