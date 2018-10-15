<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;

/**
 * Class CheckMobileDetailsLogsButtonUsageService.
 */
class CheckMobileDetailsLogsButtonUsageService {

  protected $tbo_config;
  protected $api;
  protected $log;

  /**
   * Constructs a new CheckMobileDetailsLogsButtonUsageService object.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AuditLogService $log) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->log = $log;
  }

  public function get($phone_number_origin, $categoria) {
    if(isset($_SESSION['allowedMobileAccess']) && $_SESSION['allowedMobileAccess'][$phone_number_origin]) {
      $this->log->loadName();
      $this->saveAuditLog(t('Usuario solicita consultar detalle de saldos de la línea móvil'), t('Usuario @name solicita consultar el detalle de saldos de la línea @numberphoneorigin asociada al contrato @contratid de la categoría @categoria.', ['@name' => $this->log->getName(), '@numberphoneorigin' => $phone_number_origin, '@contratid' => $_SESSION['serviceDetail']['contractId'], '@categoria' => $categoria]));

      $resp['status']='success';
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
