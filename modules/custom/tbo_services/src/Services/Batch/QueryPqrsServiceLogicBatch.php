<?php

namespace Drupal\tbo_services\Services\Batch;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class QueryPqrsServiceLogicBatch.
 *
 * @package Drupal\tbo_services\Services\Batch
 */
class QueryPqrsServiceLogicBatch {

  private $api;
  private $tboConfig;
  private $segment;
  private $currentUser;
  private $limit = 1;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Thererere.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   The service object.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api, AccountInterface $currentUser) {
    $this->tboConfig = $tboConfig;
    $this->api = $api;
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
    $this->currentUser = $currentUser;
  }

  /**
   * Get id.
   *
   * @return string
   *   The id value.
   */
  public function getId() {
    return 'get_pqrs_movil_data';
  }

  /**
   * Get row by step.
   *
   * @return int
   *   The row.
   */
  public function getRowsBySteps() {
    return 1;
  }

  /**
   * Get time in cache.
   *
   * @return int
   *   Time
   */
  public function getCacheTime() {
    return time() + 3600;
  }

  /**
   * Se inicializa el llamado de datos buscando los numeros de contrato.
   *
   * @param array $search_key
   *   The search key.
   *
   * @return array
   *   The array values.
   */
  public function prepare(array $search_key) {
    $init = 1;
    $response = [];
    // Get client data.
    $document_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    $response[0]['key'] = $document_number;
    $response[0]['key_data'] = $document_number;
    $response[0]['document_type'] = $document_type;
    $response[0]['init'] = $init;
    $response[0]['end'] = $this->limit;

    return $response;
  }

  /**
   * Llamado de los datos necesarios para el card.
   *
   * @param array $row
   *   Respuesta de prepare() separadas.
   *
   * @return array
   *   array.
   */
  public function processStep(array $row) {
    // Get timezone.
    \Drupal::logger('pqrs')->info(date_default_timezone_get() . 'movil');

    $response = [];
    $params['tokens'] = [
      'documentType' => strtolower($row['document_type']),
      'documentNumber' => $row['key'],
    ];

    try {
      $data = $this->api->getPQRSByIdsByDocumentMovil($params);
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        $response[0] = 'empty';
        return $response;
      }
      else {
        $message = UtilMessage::getMessage($e);
        $response[0] = $message['message'];
        return $response;
      }
    }

    $response = [];
    if (isset($data)) {
      $counter_data = count($data);
      $format_date = date('D M j G:i:s T Y');
      $split = explode(" ", $format_date);
      for ($i = 0; $i < $counter_data; $i++) {
        $not_data_text = t('No disponible');
        $une_cun_null = $sr_number_null = $contact_fullName_null = $status_null = 0;
        if (method_exists($not_data_text, 'getUntranslatedString')) {
          $not_data_text = $not_data_text->getUntranslatedString();
        }
        $data_value = $data[$i];
        // Translate type.
        $ticket_type = $data_value->typeRequest;
        if (isset($ticket_type)) {
          $ticket_type_translate = 'Pqrs - ' . $ticket_type;
          $ticket_type_translate = t($ticket_type_translate);
          $ticket_type = str_replace('Pqrs - ', '', $ticket_type_translate);
        }
        else {
          $ticket_type = $not_data_text;
        }

        $contact_fullName = $data_value->nameUser;
        if (!isset($contact_fullName)) {
          $contact_fullName = $not_data_text;
          $contact_fullName_null = 1;
        }
        $contact_email = isset($data_value->emailUser) ? $data_value->emailUser : $not_data_text;
        $sr_number = $data_value->numberTicket;
        if (!isset($sr_number)) {
          $sr_number = $not_data_text;
          $sr_number_null = 1;
        }
        $status = $data_value->state;
        if (!isset($status)) {
          $status = $not_data_text;
          $status_null = 1;
        }
        else {
          $status = ucwords(strtolower($status));
        }

        // Format uneCun.
        $une_cun = $data_value->numberCUN;
        if (isset($une_cun)) {
          $une_cun = str_replace('-', '', $une_cun);
        }
        else {
          $une_cun = $not_data_text;
          $une_cun_null = 1;
        }
        $product = $data_value->msisdn;
        if (isset($product)) {
          $product = $this->tboConfig->formatLine($product);
        }
        else {
          $product = $not_data_text;
        }

        // Format date.
        $opened_date = $data_value->radicationDate;
        if (isset($opened_date)) {
          $opened_date_replace = str_replace('COT', $split[4], $opened_date);
          try {
            $opened_date = $this->tboConfig->formatDate(strtotime($opened_date_replace));
          }
          catch (\Exception $e) {
            $pqrs_opened_date = str_replace('/', '-', $opened_date_replace);
            $opened_date = $this->tboConfig->formatDate(strtotime($pqrs_opened_date));
          }
        }
        else {
          $opened_date = $not_data_text;
        }

        $commit_time = $data_value->expirationDate;
        $commit_time_timestamp = 0;
        if (isset($commit_time)) {
          $commit_time_replace = str_replace('COT', $split[4], $commit_time);
          try {
            $commit_time = $this->tboConfig->formatDate(strtotime($commit_time_replace));
            $commit_time_timestamp = strtotime($commit_time);
          }
          catch (\Exception $e) {
            $pqrs_commit_time = str_replace('/', '-', $commit_time_replace);
            $commit_time = $this->tboConfig->formatDate(strtotime($pqrs_commit_time));
            $commit_time_timestamp = strtotime($commit_time);
          }
        }
        else {
          $commit_time = $not_data_text;
        }

        // Translate type.
        $une_estate_sic = $data_value->stateCun;
        if (isset($une_estate_sic)) {
          $sic_state_translate = 'Pqrs state - ' . $une_estate_sic;
          $sic_state_translate = t($sic_state_translate);
          $une_estate_sic = str_replace('Pqrs - ', '', $sic_state_translate);
        }
        else {
          $une_estate_sic = $not_data_text;
        }

        \Drupal::logger('pqrs_data_mobile')->info('Save response ' . $commit_time);

        $response['movil'][] = [
          'type' => $ticket_type,
          'request_code' => (string) $sr_number,
          'request_code_null' => $sr_number_null,
          'user' => $contact_fullName,
          'user_null' => $contact_fullName_null,
          'email' => $contact_email,
          'status' => $status,
          'status_null' => $status_null,
          'filing_date' => $opened_date,
          'due_date' => $commit_time,
          'filing_number' => (string) $une_cun,
          'filing_number_null' => $une_cun_null,
          'product_line' => $product,
          'state_case' => $une_estate_sic,
          'environment' => 'movil',
          'timestamp' => $commit_time_timestamp,
        ];
      }
    }
    else {
      $response['error'] = 'En este momento no podemos obtener la información de tus servicios fijos';
    }

    return $response;
  }

  /**
   * Get result.
   *
   * @param array $data
   *   Array data.
   *
   * @return array
   *   Array data.
   */
  public function result(array $data) {
    $response = [];

    foreach ($data as $data_key => $data_value) {
      $response = $data_value;
    }

    return $response;
  }

}
