<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class CheckMobileUsageService.
 */
class CheckMobileUsageService {

	protected $tbo_config;
	protected $api;
  protected $log;
  protected $segment;

  /**
   * Constructs a new CheckMobileDetailsUsageService object.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AuditLogService $log) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->log = $log;
    \Drupal::service('adf_segment')->segmentPhpInit();
    $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
  }

  public function get($numberPhone) {
    //  Permite consultar solo si tiene acceso a este telefono
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

      foreach($response->balances as $key => $value) {
        if($value->wallet == 'Saldo') {
          $saldo = $value->balanceAmount;
          $resp['saldo'] = $saldo;
          $resp['saldo_con_formato'] = $this->tbo_config->formatCurrency($saldo);
          $resp['actualizado'] = date('d') . ' de ' . date('M, G:i');
        }
      }
      
      $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
      $this->segment->track([
        'event' => 'TBO - Visualizar Saldos - Consulta',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Portafolio de Servicios',
          'label' => $_SESSION['serviceDetail']['category'] . ' - movil',
          'value' => round($resp['saldo']),
          'site' => 'NEW',
        ],
      ]);
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

