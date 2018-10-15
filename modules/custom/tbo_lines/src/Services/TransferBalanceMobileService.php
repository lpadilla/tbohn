<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;

/**
 * Class CheckMobileUsageService.
 */
class TransferBalanceMobileService {

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
    // Segment.
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
  }

  public function get($phone_number_origin, $phone_number_destiny, $value) {
    //guardado log auditoria
    //  Permite consultar solo si tiene accceso a este telefono
    if (!isset($_SESSION['ver_consumo'][$phone_number_origin][date("Y-m-d")]['count_intentos'])) {
      $_SESSION['ver_consumo'][$phone_number_origin][date("Y-m-d")]['count_intentos'] = 0;
    }

    $this->log->loadName();
    $this->saveAuditLog(t('Usuario solicita compartir saldo de la línea @numberphoneorigin', ['@numberphoneorigin' => $phone_number_origin]), t('Usuario @name solicita compartir saldo de la línea @numberphoneorigin asociada al contrato @contratid', ['@name' => $this->log->getName(), '@numberphoneorigin' => $phone_number_origin, '@contratid' => $_SESSION['serviceDetail']['contractId']]));

    if (isset($_SESSION['allowedMobileAccess']) && $_SESSION['allowedMobileAccess'][$phone_number_origin]) {
      if ($_SESSION['ver_consumo'][$phone_number_origin][date("Y-m-d")]['count_intentos'] < $_SESSION['ver_consumo']['numero_de_intentos']) {

        $date = date_create();

        if (floatval($_SESSION['ver_consumo']['valor_minimo_a_compartir']) <= floatval($value) && floatval($value) <= floatval($_SESSION['ver_consumo']['valor_maximo_a_compartir'])) {
          $transactionID = date_format($date, 'U');
          $params = [
            'debtMsisdn' => $phone_number_origin,
            'amount' => $value,
            'transactionId' => $transactionID,
          ];
          $jsonBody = json_encode($params);
          $params = [
            'headers' => [
              'Content-Type' => 'application/json',
            ],
            'tokens' => [
              'msisdn' => $phone_number_destiny,
            ],
            'body' => $jsonBody,
          ];

          $bnEsError = false;
          try {
            $responses = $this->api->transferBalance($params);
            $method = 'getCreditsCardByIdentification';

            $this->saveAuditLog(t('Usuario confirma transferencia de saldo de la línea @numberphoneorigin a la línea destino @phonenumberdestiny', ['@numberphoneorigin' => $phone_number_origin, '@phonenumberdestiny' => $phone_number_destiny]), t('Usuario @name confirma transferencia de saldo de la línea @numberphoneorigin a la línea destino @numberphonedestiny con Id de transacción @idtransaccion', ['@name' => $this->log->getName(), '@numberphoneorigin' => $phone_number_origin, '@numberphonedestiny' => $phone_number_destiny, '@idtransaccion' => $transactionID]));
          }
          catch (\Exception $e) {
            $responses = json_decode($e->getMessage());

            $bnEsError = true;
          }

          //\Drupal::logger('tbo_lines')->notice(print_r($responses,true));

          foreach ($responses as $clave => $response) {
            if ($clave == 'error') {
              if ($response->type == "NEG") {
                $resp['tipoDeMensaje'] = 'error';
              }
              else {
                $resp['tipoDeMensaje'] = 'alerta';
              }
              $resp['mensaje'] = t($response->reason);
            }
            if ($clave == 'state') {
              $bnEsError = false;
              break;
            }
          }

          $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
          if (!$bnEsError && $responses->state == 'OK') {
            $resp['tipoDeMensaje'] = 'exito';
            $resp['mensaje'] = t('La transacción de saldo ha sido exitosa');

            $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
            $this->segment->track([
              'event' => 'TBO - Compartir Saldo - Tx',
              'userId' => $tigoId,
              'properties' => [
                'category' => 'Portafolio de Servicios',
                'label' => 'Exitoso - movil',
                'value' => $value,
                'site' => 'NEW',
              ],
            ]);

            $_SESSION['ver_consumo'][$phone_number_origin][date("Y-m-d")]['count_intentos'] = $_SESSION['ver_consumo'][$phone_number_origin][date("Y-m-d")]['count_intentos'] + 1;
          }
          else {
            $this->generateErrorSegment($value);
          }
        }
        //  Esto es debido a que el servicio valida todo menos el que el monto sea mayor al limite, motivo por el cual este se
        //  desde aca.
        else {
          $resp['tipoDeMensaje'] = 'error';
          $resp['mensaje'] = t('Su saldo actual no es suficiente, por favor verifíquelo e intente de nuevo');
          $this->generateErrorSegment($value);
        }
      }
      else {
        $resp['tipoDeMensaje'] = 'error';
        $resp['mensaje'] = t('Ha llegado al máximo de transacciones en un día (' . $_SESSION['ver_consumo']['numero_de_intentos'] . '). Por favor intente de nuevo mañana');
        $this->generateErrorSegment($value);
      }

      return new ResourceResponse($resp);
    }
  }

  //guardado log auditoria
  public function generateErrorSegment($value) {
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId(\Drupal::currentUser()->id());
    $this->segment->track([
      'event' => 'TBO - Compartir Saldo - Tx',
      'userId' => $tigoId,
      'properties' => [
        'category' => 'Portafolio de Servicios',
        'label' => 'Fallido - movil',
        'value' => $value,
        'site' => 'NEW',
      ],
    ]);
  }

  //guardado log auditoria
  public function saveAuditLog($description, $details) {
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

