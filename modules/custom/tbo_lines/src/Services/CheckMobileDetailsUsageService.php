<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class CheckMobileDetailsUsageService.
 */
class CheckMobileDetailsUsageService {

  protected $tbo_config;
  protected $api;
  protected $log;

  /**
   * Constructs a new CheckMobileDetailsUsageService object.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AuditLogService $log) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->log = $log;
  }

  public function get($numberPhone) {
    //  Permite consultar solo si tiene accceso a este telefono
    if(isset($_SESSION['allowedMobileAccess']) && $_SESSION['allowedMobileAccess'][$numberPhone]) {
      $prefix_contry = \Drupal::config('adf_rest_api.settings')->get('prefix_country');
      $params = [
        'tokens' => [
          'msisdn' => $prefix_contry . $numberPhone,
        ],
        'query' => [
          'grouped' => '1',
        ],
      ];

      
      try {
        $response = $this->api->tolGetBalances($params);
      } catch(\Exception $e) {
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
      
      $balance = \Drupal::service('tbo_lines.consumption_balance_rest_logic')->get($numberPhone);
      
      $resp['voiceLimit'] = isset($balance['minutes']) ? $balance['minutes'] : 0;
      $resp['smsLimit'] = isset($balance['sms']) ? $balance['sms'] : 0;
      $resp['internetLimit'] = isset($balance['data']) ? $balance['data'] : 0;
      $resp['voiceActual'] = 0;
      $resp['smsActual'] = 0;
      $resp['internetActual'] = 0;    
      
      foreach($response->balances as $key => $value) {
        if(strtolower($value->category) == 'voice') {
          $resp['voiceActual'] += floatval($value->amount);
        }
        if(strtolower($value->category) == 'sms') {
          $resp['smsActual'] += floatval($value->amount);
        }
        if(strtolower($value->category) == 'internet') {
          $resp['internetActual'] += floatval($value->amount);
        }
      }
      $resp['internetActual'] = $resp['internetActual']/1000;

      $resp['voicePercentage'] = round(($resp['voiceActual']/$resp['voiceLimit'])*100,2) . '%';
      $resp['smsPercentage'] = round(($resp['smsActual']/$resp['smsLimit'])*100,2) . '%';
      $resp['internetPercentage'] = round(($resp['internetActual']/$resp['internetLimit'])*100,2) . '%';

      $resp['voiceLimit'] = round($resp['voiceLimit'],2);
      $resp['voiceActual'] = round($resp['voiceActual'],2);
      $resp['smsLimit'] = round($resp['smsLimit'],2);
      $resp['smsActual'] = round($resp['smsActual'],2);
      $resp['internetLimit'] = round($resp['internetLimit'],2);
      $resp['internetActual'] = round($resp['internetActual'],2);
      
      return new ResourceResponse($resp);
    }
  }
  
  //guardado log auditoria
  public function saveAuditLog($description, $details) {
    $this->log->loadName();
    //Create array data[]
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'event_type' => t('Servicios'),      
      'description' => $description,
      'details' => $details,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    //Save audit log
    $this->log->insertGenericLog($data);
  }
  
}
