<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;

/**
 * Class CheckMobileUsageCancelTranferService.
 */
class CheckMobileUsageCancelTranferService {

	protected $tbo_config;
	protected $api;
  protected $log;
  protected $segment;

  /**
   * Constructs a new CheckMobileUsageCancelTranfer object.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AuditLogService $log) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->log = $log;
    \Drupal::service('adf_segment')->segmentPhpInit();
    $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
  }

  public function get($phone_number_origin, $phone_number_destiny, $value) {
    $resp['status'] = 'error';
    //  Permite consultar solo si tiene accceso a este telefono
    if(isset($_SESSION['allowedMobileAccess']) && $_SESSION['allowedMobileAccess'][$phone_number_origin]) {

      $this->log->loadName();
      $this->saveAuditLog(t('Usuario cancela confirmación de transferencia de saldo de la línea @numberphoneorigin a la línea destino @numberphonedestiny',['@numberphoneorigin' => $phone_number_origin, '@numberphonedestiny' => $phone_number_destiny]), t('Usuario @name cancela confirmación de transferencia de saldo de la línea @numberphoneorigin a la línea destino @numberphonedestiny.', ['@name' => $this->log->getName(), '@numberphoneorigin' => $phone_number_origin, '@numberphonedestiny' => $phone_number_destiny]));
      
      $resp['status'] = 'ok';
      
    }
    
    return new ResourceResponse($resp);
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

