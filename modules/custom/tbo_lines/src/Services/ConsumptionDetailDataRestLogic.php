<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Masterminds\HTML5\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountProxyInterface;

class ConsumptionDetailDataRestLogic {
  
  protected $api;
  protected $currentUser;
  protected $tbo_config;
  
  /**
   * ConsumptionDetailDataRestLogic constructor.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AccountProxyInterface $current_user) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->currentUser = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get($parameters) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $dates = \Drupal::service('tbo_lines.consumption_filter_service')
      ->getInitFinalDates();
    
    $dates_to_show = $dates['dates_bloqued'];
    $dates_to_show;
    
    $params = [
      'query' => [
        'dateFrom' => $dates['date_ini'],
        'dateTill' => $dates['date_end'],
        //'dateFrom' => '',
        //'dateTill' => '',
      ],
      'tokens' => [
        'msisdn' => $_SESSION['serviceDetail']['address'],
      ],
    ];
    $response = [];
    $result_show = [];
    $result_show_m = [];
    $result_file = [];
    $result = [];
    
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_lines');
    $table_mobile = $tempstore->get('data_table_mobile');
    
    try {
      // llamado al servicio smsGprsMmsDetail
      $response = $this->api->smsGprsMmsDetail($params)->response;
      $collection_data = $response->SmsGprsMmsDetailResponse->body->smsGprsMmsDetailList->detail;
      asort($parameters);
      
      // tratamiento de la respuesta
      foreach ($collection_data as $detail) {
        if ($detail->messageDescription == 'GPRS') {
          $aux_array = [];
          $aux_array_m = [];
          //array_push($array_file, [$this->tbo_config->formatDate(strtotime($detail->eventDateTime)), 'Pendiente la hora', intval($detail->eventDuration) / 1000 .' MB' ]);
          
          $date = '';
          $hour = '';
          $consumption = '';
          
          foreach ($parameters as $key => $value) {
            $aux_value = '';
            switch ($key) {
              case 'date':
                $aux_array[$key . '_format'] = $this->tbo_config->formatDate(strtotime($detail->eventDateTime));
                break;
              case 'hour':
                $aux_array[$key] = $this->tbo_config->formatHour(strtotime($detail->eventDateTime));
                break;
              case 'consumption':
                $consumption = intval($detail->eventDuration) / 1000;
                $aux_array[$key] = $consumption . ' MB';
                break;
            }
          }
          
          $aux_array['date'] = date('Y-m-d', strtotime($detail->eventDateTime));
          $aux_array['download'] = [
            $this->tbo_config->formatDate(strtotime($detail->eventDateTime)),
            $this->tbo_config->formatHour(strtotime($detail->eventDateTime)),
            strval(intval($detail->eventDuration) / 1000) . ' MB'
          ];
          
          foreach ($table_mobile as $index => $item) {
            switch ($index) {
              case 'date_hour':
                if ($table_mobile['date_hour']['show']) {
                  $aux_array_m[$index . '_format'] = $this->tbo_config->formatDate(strtotime($detail->eventDateTime)) . ' ' . $this->tbo_config->formatHour(strtotime($detail->eventDateTime));
                }
                break;
              case 'consumption':
                if ($table_mobile['consumption']['show']) {
                  $consumption = intval($detail->eventDuration) / 1000;
                  $aux_array_m[$index] = $consumption . ' MB';
                }
                break;
            }
          }
          
          $aux_array_m['date'] = date('Y-m-d', strtotime($detail->eventDateTime));
          
          if(in_array($aux_array['date'], $dates_to_show)){
            array_push($result_show, $aux_array);
            array_push($result_show_m, $aux_array_m);
          }
        }
      }
      
      $params_type = [
        'tokens' => [
          'msisdn' => $_SESSION['serviceDetail']['address'],
        ],
      ];
      array_push($result, $result_show);
      array_push($result, $result_show_m);
    }
    catch (Exception $e) {
    }
    return ($result);
  }
}
