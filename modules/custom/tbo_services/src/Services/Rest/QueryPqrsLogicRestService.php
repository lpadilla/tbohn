<?php

namespace Drupal\tbo_services\Services\Rest;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;

/**
 * Class ServicePortfolioService.
 *
 * @package Drupal\tbo_services\Services\Rest
 */
class QueryPqrsLogicRestService {

  protected $api;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;

  /**
   * QueryPqrsLogicRestService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   The b2b generally config.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   The api interface.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api) {
    $this->tboConfig = $tboConfig;
    $this->api = $api;
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
  }

  /**
   * Implements method get().
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current User.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The data.
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Get client data.
    $document_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    $document_type = strtoupper($document_type);
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    // Change document type.
    switch ($document_type) {
      case 'NIT':
        $document_type = 'NT';
    }

    // Parameters for service.
    $params['query'] = [
      'docId' => (int) $document_number,
      'docType' => $document_type,
    ];

    try {
      if (method_exists($this->api, 'getPQRSByIdsByDocumentFixed')) {
        $data = $this->api->getPQRSByIdsByDocumentFixed($params);
      }
      else {
        throw new \Exception('No se encuentra el servicio getPQRSByIdsByDocumentFixed', 500);
      }
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        $response[0] = 'empty';
        return new ResourceResponse($response);
      }
      else {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
    }
    $response = [];
    if (isset($data)) {
      $pqr_collection = $data->pqrCollection;
      foreach ($pqr_collection as $data_key => $data_value) {
        $ticket_type = $data_value->iNSProduct;
        if ($data_value->ticketType != 'Queja Da??o' && $data_value->ticketType != 'Queja Daño') {
          $not_data_text = t('No disponible');
          $sr_number_null = $une_cun_null = $contact_fullName_null = $status_null = 0;
          if (method_exists($not_data_text, 'getUntranslatedString')) {
            $not_data_text = $not_data_text->getUntranslatedString();
          }

          // Translate type.
          if (isset($ticket_type)) {
            $ticket_type_translate = 'Pqrs - ' . $ticket_type;
            $ticket_type_translate = t($ticket_type_translate);
            $ticket_type = str_replace('Pqrs - ', '', $ticket_type_translate);
          }
          else {
            $ticket_type = $not_data_text;
          }

          $contact_fullName = $data_value->contactFullName;
          if (!isset($contact_fullName)) {
            $contact_fullName = $not_data_text;
            $contact_fullName_null = 1;
          }
          $contact_email = isset($data_value->contactEmail) ? $data_value->contactEmail : $not_data_text;
          $sr_number = $data_value->srNumber;
          if (!isset($sr_number)) {
            $sr_number = $not_data_text;
            $sr_number_null = 1;
          }
          $status = $data_value->status;
          if (!isset($status)) {
            $status = $not_data_text;
            $status_null = 1;
          }
          else {
            $status = ucwords(strtolower($status));
          }

          // Format uneCun.
          $une_cun = $data_value->uneCUN;
          if (isset($une_cun)) {
            $une_cun = str_replace('-', '', $une_cun);
          }
          else {
            $une_cun = $not_data_text;
            $une_cun_null = 1;
          }
          $product = isset($data_value->product) ? $data_value->product : $not_data_text;

          // Format date.
          $opened_date = $data_value->openedDate;
          if (isset($opened_date)) {
            try {
              $opened_date = $this->tboConfig->formatDate(strtotime($opened_date));
            }
            catch (\Exception $e) {
              $pqrs_opened_date = str_replace('/', '-', $data_value->openedDate);
              $opened_date = $this->tboConfig->formatDate(strtotime($pqrs_opened_date));
            }
          }
          else {
            $opened_date = $not_data_text;
          }

          $commit_time = $data_value->commitTime;
          $commit_time_timestamp = 0;
          if (isset($commit_time)) {
            try {
              $commit_time = $this->tboConfig->formatDate(strtotime($commit_time));
              $commit_time_timestamp = strtotime($commit_time);
            }
            catch (\Exception $e) {
              $pqrs_commit_time = str_replace('/', '-', $data_value->commitTime);
              $commit_time = $this->tboConfig->formatDate(strtotime($pqrs_commit_time));
              $commit_time_timestamp = strtotime($commit_time);
            }
          }
          else {
            $commit_time = $not_data_text;
          }

          // Translate type.
          $une_estate_sic = $data_value->uneEstadoSIC;
          if (isset($une_estate_sic)) {
            $sic_state_translate = 'Pqrs state - ' . $une_estate_sic;
            $sic_state_translate = t($sic_state_translate);
            $une_estate_sic = str_replace('Pqrs state - ', '', $sic_state_translate);
          }
          else {
            $une_estate_sic = $not_data_text;
          }

          $response['fixed'][] = [
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
            'environment' => 'fijo',
            'timestamp' => $commit_time_timestamp,
          ];
        }
      }
    }
    else {
      $response['error'] = 'En este momento no podemos obtener la información de tus servicios fijos';
    }

    if (empty($response)) {
      $response[0] = 'empty';
      return new ResourceResponse($response);
    }

    return new ResourceResponse($response);
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   User actual.
   * @param array $params
   *   Data of user.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The data response.
   */
  public function post(AccountProxyInterface $currentUser, array $params) {
    $this->currentUser = $currentUser;
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data_log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'PQRs',
      'description' => t('Usuario consulta detalle de PQRs'),
      'details' => t('Usuario @userName consulta detalle de PQRs asociado al Código de solicitud @requestCode',
        [
          '@userName' => $service->getName(),
          '@requestCode' => $params['requestCode'],
        ]
      ),
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);

    // Set segment variable.
    try {
      $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
      $segment_track = [
        'event' => 'TBO - Consulta Detalle Pqrs - Consulta',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Pqrs',
          'label' => $params['environment'],
          'site' => 'NEW',
        ],
      ];

      $this->segment->track($segment_track);
    }
    catch (\Exception $e) {
      // Save Drupal log.
    }

    return new ResourceResponse('Ok');

  }

}
