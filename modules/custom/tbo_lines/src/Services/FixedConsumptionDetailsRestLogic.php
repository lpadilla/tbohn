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

class FixedConsumptionDetailsRestLogic {
  
  protected $api;
  protected $currentUser;
  protected $tbo_config;
  
  /**
   * FixedConsumptionDetailsRestLogic constructor.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AccountProxyInterface $current_user) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->currentUser = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get($type, $date_parameter) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $final_date = date('d-m-Y');
    $aux_time = strtotime('-2 month', strtotime($final_date));
    $aux_time_1 = strtotime('-1 month', strtotime($final_date));
    $init_date = '01-' . date('m', $aux_time) . '-' . date('Y', $aux_time);
    $consumption_by_month = [];
    $months_array = [
      $this->getMonthName(date('m', $aux_time)),
      $this->getMonthName(date('m', $aux_time_1)),
      $this->getMonthName(date('m'))
    ];
    $line = substr_replace($_SESSION['serviceDetail']['measuringElement'], '0', 0, 2);
    
    $params = [
      'tokens' => [
        'contractId' => $_SESSION['serviceDetail']['contractId'],
        'line' => $line,
      ],
      'query' => [
        'fromDate' => $init_date,
        'toDate' => $final_date,
      ],
    ];
    
    $response_local = '';
    $response_calls = '';
    
    try {
      $response_local = $this->api->getLocalCallsByContractIdAndMeasuringElement($params);
    }
    catch (\Exception $e) {
      $response_local = FALSE;
    }
    
    try {
      $response_calls = $this->api->getCallsByContractIdAndMeasuringElement($params);
    }
    catch (\Exception $e) {
      $response_calls = FALSE;
    }
    
    switch ($type) {
      //card histograma de consumos
      case 'histogram':
        $tempstore = \Drupal::service('user.private_tempstore')
          ->get('tbo_lines');
        $config = $tempstore->get('fixed_consumption_histogram');
        
        foreach ($response_local as $call) {
          $time = strtotime(substr($call->dateTimeStart, 0, 10));
          $date = date('m', $time);
          $monthName = $this->getMonthName($date);
          $consumption_by_month [$monthName]['local_minutes'] = $consumption_by_month [$monthName]['local_minutes'] + $call->duration;
        }
        
        foreach ($response_calls as $call) {
          $time = strtotime(substr($call->dateTimeStart, 0, 10));
          $date = date('m', $time);
          $monthName = $this->getMonthName($date);
          
          switch ($call->destinationType) {
            case 'UNE':
              $key = 'minutes_nal_UNE';
              break;
            
            case 'OTROS':
              $key = 'minutes_nal_others';
              break;
            
            default :
              $key = 'minutes_internal';
              break;
          }
          $consumption_by_month [$monthName][$key] = $consumption_by_month [$monthName][$key] + $call->duration;
        }
        
        $columns = [];
        $colors = [];
        $values = [];
        $values_to_show = [];
        
        foreach ($config as $key => $column) {
          if ($column['show'] == 1) {
            $columns[$key] = $column['label'];
            $colors[] = $column['color'];
            $values_to_show[] = $key;
          }
        }
        
        $final_values = [];
        
        foreach ($columns as $key => $value) {
          $aux_array = [
            isset($consumption_by_month[$months_array[0]][$key]) ? $consumption_by_month[$months_array[0]][$key] : 0,
            isset($consumption_by_month[$months_array[1]][$key]) ? $consumption_by_month[$months_array[1]][$key] : 0,
            isset($consumption_by_month[$months_array[2]][$key]) ? $consumption_by_month[$months_array[2]][$key] : 0,
          ];
          $final_values [] = $aux_array;
        }
        
        $response = [
          'labels' => $months_array,
          'series' => array_values($columns),
          'values' => $final_values,
          'colors' => $colors,
        ];
        return $response;
        break;
      
      //datos para card detalle diario
      case 'DateMinutes':
        $tempstore = \Drupal::service('user.private_tempstore')
          ->get('tbo_lines');
        $config = $tempstore->get('fixed_consumption_date');
        
        foreach ($response_local as $call) {
          $time = strtotime(substr($call->dateTimeStart, 0, 10));
          $date = date('m', $time);
          $monthName = $this->getMonthName($date);
          $consumption_by_month [$monthName]['local_minutes'] = $consumption_by_month [$monthName]['local_minutes'] + $call->duration;
        }
        
        foreach ($response_calls as $call) {
          $time = strtotime(substr($call->dateTimeStart, 0, 10));
          $date = date('m', $time);
          $monthName = $this->getMonthName($date);
          
          switch ($call->destinationType) {
            case 'UNE':
              $key = 'minutes_nal_UNE';
              break;
            
            case 'OTROS':
              $key = 'minutes_nal_others';
              break;
            
            default :
              $key = 'minutes_internal';
              break;
          }
          $consumption_by_month [$monthName][$key] = $consumption_by_month [$monthName][$key] + $call->duration;
        }
        
        $columns = [];
        $colors = [];
        $values = [];
        $values_to_show = [];
        
        foreach ($config as $key => $column) {
          if ($column['show'] == 1) {
            $columns[$key] = $column['label'];
            $colors[] = $column['color'];
            $values_to_show[] = $key;
          }
        }
        
        foreach ($months_array as $month) {
          $aux_array = [];
          
          foreach ($values_to_show as $value) {
            $aux_array [] = isset($consumption_by_month[$month][$value]) ? $this->tbo_config->formatUnit($consumption_by_month[$month][$value]) : $this->tbo_config->formatUnit(0);
          }
          
          $values [$month] = $aux_array;
        }
        $response = [
          'values' => $values,
        ];
        return $response;
        break;
      
      //card histórico de consumos por día
      case 'daily':
        $date_control = date('d-m-Y', intval($date_parameter));
        setlocale(LC_ALL, 'es_ES');
        $date_mobile = date('d M, Y', strtotime($date_control));
        $consumption_by_resource = [];
        $consumption_by_resource_m = [];
        $tempstore = \Drupal::service('user.private_tempstore')
          ->get('tbo_lines');
        $config_daily = $tempstore->get('fixed_consumption_daily');
        $config_daily_m = $tempstore->get('fixed_consumption_daily_m');
        $format_hour = $tempstore->get('fixed_consumption_daily_hour');
        $response_local = $this->orderResponseByDate($response_local);
        $response_calls = $this->orderResponseByDate($response_calls);
        
        $data_download = [];
        
        foreach ($response_local as $call) {
          $aux_resource = [];
          $aux_resource_m = [];
          $time = strtotime(substr($call->dateTimeStart, 0, 10));
          $date = date('d-m-Y', $time);
          
          //datos para la descarga
          $date_minor = '01-' . date('m-Y', strtotime($date_control));
          $date_mayor = strtotime('+1 month', strtotime($date_minor));
          
          if ((strtotime($date) >= strtotime($date_minor)) && (strtotime($date) < $date_mayor)) {
            $data_download[] = [
              $this->tbo_config->formatDate(strtotime($call->dateTimeStart)),
              $this->generalformat(strtotime($call->dateTimeStart), $format_hour),
              'Minutos locales',
              $call->duration,
              substr_replace($call->origin, '57', 0, 1),
              substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination,
            ];
          }
          
          if ($date_control == $date) {
            
            foreach ($config_daily as $column) {
              if ($column['show'] == 1) {
                switch ($column['service_field']) {
                  case 'date':
                    $aux_resource['date'] = $this->tbo_config->formatDate(strtotime($call->dateTimeStart));
                    break;
                  case 'hour':
                    $aux_resource['hour'] = $this->generalformat(strtotime($call->dateTimeStart), $format_hour);
                    break;
                  case 'minutes':
                    $aux_resource['minutes'] = $call->duration;
                    break;
                  case 'origin':
                    $aux_resource['origin'] = substr($call->origin, 0, 1) == 0 ? substr_replace($call->origin, '57', 0, 1) : $call->origin;
                    break;
                  case 'destination':
                    $aux_resource['destination'] = substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination;
                    break;
                }
              }
            }
            
            $aux_resource['timestamp'] = strtotime($call->dateTimeStart);
            
            foreach ($config_daily_m as $column) {
              if ($column['show'] == 1) {
                switch ($column['service_field']) {
                  case 'hour':
                    $aux_resource_m['hour'] = $this->generalformat(strtotime($call->dateTimeStart), $format_hour);
                    break;
                  case 'minutes':
                    $aux_resource_m['minutes'] = $call->duration;
                    break;
                  case 'origin':
                    $aux_resource_m['origin'] = substr($call->origin, 0, 1) == 0 ? substr_replace($call->origin, '57', 0, 1) : $call->origin;
                    break;
                  case 'destination':
                    $aux_resource_m['destination'] = substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination;
                    break;
                }
              }
            }
            $aux_resource_m['timestamp'] = strtotime($call->dateTimeStart);
          }
          
          if (count($aux_resource) > 0) {
            $consumption_by_resource['local_minutes'][] = $aux_resource;
          }
          
          if (count($aux_resource_m) > 0) {
            $consumption_by_resource_m['local_minutes'][] = $aux_resource_m;
          }
        }
        
        foreach ($response_calls as $call) {
          $aux_resource = [];
          $aux_resource_m = [];
          $time = strtotime(substr($call->dateTimeStart, 0, 10));
          $date = date('d-m-Y', $time);
          
          switch ($call->destinationType) {
            case 'UNE':
              $key = 'minutes_nal_UNE';
              $key_download = 'Minutos nacionales UNE';
              break;
            
            case 'OTROS':
              $key = 'minutes_nal_others';
              $key_download = 'Minutos nacionales otros';
              break;
            
            default :
              $key = 'minutes_internal';
              $key_download = 'Minutos internacionales';
              break;
          }
          
          $date_minor = '01-' . date('m-Y', strtotime($date_control));
          $date_mayor = strtotime('+1 month', strtotime($date_minor));
          
          if ((strtotime($date) >= strtotime($date_minor)) && (strtotime($date) < $date_mayor)) {
            $data_download[] = [
              $this->tbo_config->formatDate(strtotime($call->dateTimeStart)),
              $this->generalformat(strtotime($call->dateTimeStart), $format_hour),
              $key_download,
              $call->duration,
              substr_replace($call->origin, '57', 0, 1),
              substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination,
            ];
          }
          
          if ($date_control == $date) {
            foreach ($config_daily as $column) {
              if ($column['show'] == 1) {
                switch ($column['service_field']) {
                  case 'date':
                    $aux_resource['date'] = $this->tbo_config->formatDate(strtotime($call->dateTimeStart));
                    break;
                  case 'hour':
                    $aux_resource['hour'] = $this->generalformat(strtotime($call->dateTimeStart), $format_hour);
                    break;
                  case 'minutes':
                    $aux_resource['minutes'] = $call->duration;
                    break;
                  case 'origin':
                    $aux_resource['origin'] = substr($call->origin, 0, 1) == 0 ? substr_replace($call->origin, '57', 0, 1) : $call->origin;
                    break;
                  case 'destination':
                    $aux_resource['destination'] = substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination;
                    break;
                }
              }
            }
            
            $aux_resource['timestamp'] = strtotime($call->dateTimeStart);
            
            foreach ($config_daily_m as $column) {
              if ($column['show'] == 1) {
                switch ($column['service_field']) {
                  case 'hour':
                    $aux_resource_m['hour'] = $this->generalformat(strtotime($call->dateTimeStart), $format_hour);
                    break;
                  case 'minutes':
                    $aux_resource_m['minutes'] = $call->duration;
                    break;
                  case 'origin':
                    $aux_resource_m['origin'] = substr($call->origin, 0, 1) == 0 ? substr_replace($call->origin, '57', 0, 1) : $call->origin;
                    break;
                  case 'destination':
                    $aux_resource_m['destination'] = substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination;
                    break;
                }
              }
            }
            $aux_resource_m['timestamp'] = strtotime($call->dateTimeStart);
          }
          
          if (count($aux_resource) > 0) {
            $consumption_by_resource[$key][] = $aux_resource;
          }
          
          if (count($aux_resource_m) > 0) {
            $consumption_by_resource_m[$key][] = $aux_resource_m;
          }
        }
        
        return [
          'desktop' => $consumption_by_resource,
          'mobile' => $consumption_by_resource_m,
          'date_mobile' => $date_mobile,
          'data_download' => $data_download,
        ];
        break;
      
      //card histórico de consumos por mes
      case 'month':
        $tempstore = \Drupal::service('user.private_tempstore')
          ->get('tbo_lines');
        $config_table = $tempstore->get('data_fixed_month_table');
        $config_table_m = $tempstore->get('data_fixed_month_table_m');
        $config_date_format = $tempstore->get('data_fixed_month_date_format');
        $config_hour_format = $tempstore->get('data_fixed_month_hour_format');
        $config_month = $tempstore->get('data_fixed_month');
        $result_m = [];
        $aux_months = [
          'enero' => 1,
          'febrero' => 2,
          'marzo' => 3,
          'abril' => 4,
          'mayo' => 5,
          'junio' => 6,
          'julio' => 7,
          'agosto' => 8,
          'septiembre' => 9,
          'octubre' => 10,
          'noviembre' => 11,
          'diciembre' => 12,
        ];
        
        $response_by_date = [];
        $download_resume = [];
        $download_detail = [];
        $response_by_date = [];
        
        foreach ($response_local as $call) {
          if (intval(date('m', strtotime($call->dateTimeStart))) == $aux_months[strtolower($date_parameter)]) {
            $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')]['m_locals'] = $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')]['m_locals'] + intval($call->duration);
            $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')]['date'] = $this->generalformat(strtotime($call->dateTimeStart), $config_date_format);
            $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')]['timestamp'] = strtotime($call->dateTimeStart);
            
            $download_detail [] = [
              $this->generalformat(strtotime($call->dateTimeStart), $config_date_format),
              'Minutos locales',
              $this->generalformat(strtotime($call->dateTimeStart), $config_hour_format),
              $call->duration,
              substr_replace($call->origin, '57', 0, 1),
              substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination,
            ];
          }
        }
        
        foreach ($response_calls as $call) {
          if (intval(date('m', strtotime($call->dateTimeStart))) == $aux_months[strtolower($date_parameter)]) {
            switch ($call->destinationType) {
              case 'UNE':
                $key = 'm_nacionales_une';
                $key2 = 'Minutos nacionales Une';
                break;
              
              case 'OTROS':
                $key = 'm_nacionales_others';
                $key2 = 'Minutos nacionales otros';
                break;
              
              default :
                $key = 'm_international';
                $key2 = 'Minutos internacionales';
                break;
            }
            $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')][$key] = $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')][$key] + intval($call->duration);
            $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')]['date'] = $this->generalformat(strtotime($call->dateTimeStart), $config_date_format);
            $response_by_date[$this->generalformat(strtotime($call->dateTimeStart), 'd-m-Y')]['timestamp'] = strtotime($call->dateTimeStart);
            
            $download_detail [] = [
              $this->generalformat(strtotime($call->dateTimeStart), $config_date_format),
              $key2,
              $this->generalformat(strtotime($call->dateTimeStart), $config_hour_format),
              $call->duration,
              substr_replace($call->origin, '57', 0, 1),
              substr($call->destination, 0, 1) == 0 ? substr_replace($call->destination, '', 0, 1) : $call->destination,
            ];
          }
        }
        
        foreach ($response_by_date as $item) {
          $aux_item = [];
          $sum_resources = 0;
          $sum_resources_m = 0;
          
          $download_resume [] = [
            $item['date'],
            isset($item['m_locals']) ? $item['m_locals'] : 0,
            isset($item['m_nacionales_une']) ? $item['m_nacionales_une'] : 0,
            isset($item['m_nacionales_others']) ? $item['m_nacionales_others'] : 0,
            isset($item['m_international']) ? $item['m_international'] : 0,
          ];
          
          foreach ($config_table as $column) {
            if ($column['show'] == 1) {
              $aux_item[$column['service_field']] = isset($item[$column['service_field']]) ? $item[$column['service_field']] : 0;
              if ($column['service_field'] != 'date') {
                $sum_resources = $aux_item[$column['service_field']] + $sum_resources;
              }
            }
          }
          
          $aux_item['timestamp'] = $item['timestamp'];
          
          if ($sum_resources > 0) {
            $result[] = $aux_item;
          }
          
          $aux_item_m = [];
          foreach ($config_table_m as $column) {
            if ($column['show'] == 1) {
              $aux_item_m[$column['service_field']] = isset($item[$column['service_field']]) ? $item[$column['service_field']] : 0;
              if ($column['service_field'] != 'date') {
                $sum_resources_m = $aux_item_m[$column['service_field']] + $sum_resources_m;
              }
            }
          }
          
          $aux_item_m['timestamp'] = $item['timestamp'];
          
          if ($sum_resources_m > 0) {
            $result_m[] = $aux_item_m;
          }
        }
        
        return [
          'desktop' => $result,
          'mobile' => $result_m,
          'data_download' => [
            'resume' => $download_resume,
            'detail' => $download_detail,
          ],
        ];
        break;
    }
  }
  
  public function post($data) {
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    
    //Create array data[]
    
    if ($data['type'] == 'month') {
      $log = [
        'companyName' => $_SESSION['company']['name'],
        'companyDocument' => $_SESSION['company']['nit'],
        'event_type' => t('Servicios'),
        'description' => t('Usuario consulta historial de consumo por mes de servicio fijo'),
        'details' => t('Usuario @usuario consulta historial de consumo del mes @mes del contrato @contract de la dirección @address ',
          [
            '@usuario' => $name,
            '@address' => isset ($_SESSION['serviceDetail']['address']) ? $_SESSION['serviceDetail']['address'] : 'No disponible',
            '@contract' => isset ($_SESSION['serviceDetail']['contractId']) ? $_SESSION['serviceDetail']['contractId'] : 'No disponible',
            '@plan' => isset ($_SESSION['serviceDetail']['plan']) ? $_SESSION['serviceDetail']['plan'] : 'No disponible',
            '@mes' => $data['month'],
          ]),
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
      ];
    }
    
    if ($data['type'] == 'daily') {
      
      $month = $this->getMonthName(date('m', strtotime($data['month'])));
      
      $log = [
        'companyName' => $_SESSION['company']['name'],
        'companyDocument' => $_SESSION['company']['nit'],
        'event_type' => t('Servicios'),
        'description' => t('Usuario descarga reporte de historial de consumos fijos detallado por mes desde el historico por día'),
        'details' => t('Usuario @usuario desacarga reporte @format de consumos del mes @mes de servicios fijos asociados al contrato contrato @contract de la dirección @address ',
          [
            '@usuario' => $name,
            '@address' => isset ($_SESSION['serviceDetail']['address']) ? $_SESSION['serviceDetail']['address'] : 'No disponible',
            '@contract' => isset ($_SESSION['serviceDetail']['contractId']) ? $_SESSION['serviceDetail']['contractId'] : 'No disponible',
            '@format' => $data['format'],
            '@mes' => $month,
          ]),
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
      ];
    }
    
    if ($data['type'] == 'month_c') {
      
      $month = $data['month'];
      
      $log = [
        'companyName' => $_SESSION['company']['name'],
        'companyDocument' => $_SESSION['company']['nit'],
        'event_type' => t('Servicios'),
        'description' => t('Usuario descarga reporte de historial de consumos fijos detallado por mes desde el historico mensual'),
        'details' => t('Usuario @usuario desacarga reporte @format de consumos resumidos y detallados de servicio fijo por mes @mes asociados al contrato contrato @contract de la dirección @address ',
          [
            '@usuario' => $name,
            '@address' => isset ($_SESSION['serviceDetail']['address']) ? $_SESSION['serviceDetail']['address'] : 'No disponible',
            '@contract' => isset ($_SESSION['serviceDetail']['contractId']) ? $_SESSION['serviceDetail']['contractId'] : 'No disponible',
            '@format' => $data['format'],
            '@mes' => $month,
          ]),
        'old_value' => t('No disponible'),
        'new_value' => t('No disponible'),
      ];
    }
    
    //Save audit log
    $service_log->insertGenericLog($log);
    //return build
    return $data;
  }
  
  public function getMonthName($monthNumber) {
    $aux_months = [
      '01' => 'Enero',
      '02' => 'Febrero',
      '03' => 'Marzo',
      '04' => 'Abril',
      '05' => 'Mayo',
      '06' => 'Junio',
      '07' => 'Julio',
      '08' => 'Agosto',
      '09' => 'Septiembre',
      '10' => 'Octubre',
      '11' => 'Noviembre',
      '12' => 'Diciembre',
    ];
    //setlocale(LC_ALL, 'es_ES');
    //$monthName = strftime('%B', mktime(0, 0, 0, $monthNumber));
    return $aux_months[$monthNumber];
  }
  
  public function generalformat($hour, $format) {
    $hour_formatter = \Drupal::service('date.formatter');
    return $hour_formatter->format($hour, '', $format);
  }
  
  public function orderResponseByDate($data) {
    usort($data, function ($a1, $a2) {
      $v1 = strtotime($a1->dateTimeStart);
      $v2 = strtotime($a2->dateTimeStart);
      return $v2 - $v1; // $v2 - $v1 to reverse direction
    });
    return $data;
  }
}
