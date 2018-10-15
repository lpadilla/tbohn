<?php

namespace Drupal\tbo_services\Services\Rest;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Class QueryTechnicalSupportLogicRestService.
 *
 * @package Drupal\tbo_services\Services\Rest
 */
class QueryTechnicalSupportLogicRestService {

  protected $api;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;

  /**
   * QueryTechnicalSupportLogicRestService constructor.
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
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    // Parameters for service.
    $params['tokens'] = [
      'clientId' => (int) $document_number,
    ];

    try {
      if (method_exists($this->api, 'getSubscriberDevicesByCi')) {
        $data = $this->api->getSubscriberDevicesByCi($params);
      }
      else {
        throw new \Exception('No se encuentra el servicio getSubscriberDevicesByCi', 500);
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
      $response = $this->buildData($data);
    }

    if (empty($response)) {
      $response[0] = 'empty';
      return new ResourceResponse($response);
    }

    // Save in session key to export.
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_technical_support');

    $data_export = [
      'data' => $response,
    ];
    try {
      $tempStore->set('tbo_query_technical_support_data_' . md5($document_number), $data_export);
    }
    catch (\Exception $e) {
      $data_export['data'] = FALSE;
      $tempStore->set('tbo_query_technical_support_data_' . md5($document_number), $data_export);
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

    $description = t('Usuario consulta detalle de orden de soporte técnico');
    $detail = t('Usuario @userName consulta detalle de orden de soporte técnico asociado
al número de orden @order de la línea móvil @line',
      [
        '@userName' => $service->getName(),
        '@order' => $params['order'],
        '@line' => $params['line_number'],
      ]
    );

    // Download file.
    $file_download = '';
    if (isset($params['download'])) {
      $description = t('Usuario solicita descargar reporte con el detalle de las ordenes de soporte
técnico');
      $detail = t('Usuario @userName descarga reporte con el detalle de las ordenes de soporte técnico 
      en el formato @format',
        [
          '@userName' => $service->getName(),
          '@format' => $params['type'],
        ]
      );

      $file_download = $this->download($params['type']);
    }

    // Create array data_log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Soporte Técnico',
      'description' => $description,
      'details' => $detail,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);

    // Set segment variable.
    $event = 'TBO - Consulta Detalle STM - Consulta';
    $category = 'Soporte Técnico Móvil';
    $label = 'movil';

    if (isset($params['download'])) {
      $event = 'TBO - Descargar Archivo STM - Tx';
    }

    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);

    // Download file.
    if (isset($params['download'])) {
      return new ResourceResponse($file_download);
    }

    return new ResourceResponse('Ok');

  }

  /**
   * Implements download().
   *
   * @param string $type
   *   The download type.
   *
   * @return array
   *   The data to download.
   */
  public function download($type) {
    $document_number = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';

    if (!isset($document_number) || $document_number == '') {
      throw new AccessDeniedHttpException();
    }

    // Get data.
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_technical_support');
    $data_headers = $tempStore->get('tbo_query_technical_support_labels_' . md5($document_number));
    $config_data = $tempStore->get('tbo_query_technical_support_data_' . md5($document_number));

    $data = [];
    if (isset($config_data['data']) && $config_data['data']) {
      $data = $config_data['data']['fixed'];
    }
    else {
      // Parameters for service.
      $params['tokens'] = [
        'clientId' => (int) $document_number,
      ];

      try {
        if (method_exists($this->api, 'getSubscriberDevicesByCi')) {
          $getData = $this->api->getSubscriberDevicesByCi($params);
        }
        else {
          throw new \Exception('No se encuentra el servicio getSubscriberDevicesByCi', 500);
        }
      }
      catch (\Exception $e) {
        // Return message in rest.
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
      if (isset($getData)) {
        $data = $this->buildData($getData);
        $data = $data['fixed'];
      }
    }

    // Starting file building.
    $type_file = isset($type) ? $type : 'csv';

    // Created file path.
    $dir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    $doc_name = "soporte tecnico movil";

    $date = \Drupal::service('date.formatter')->format(time(), 'custom', '_Y-m-d');
    $file_name = $doc_name . $date . '.' . $type_file;
    $path = $dir . '/' . $file_name;

    // Prepare file.
    if ($type_file == 'xlsx' || $type_file == 'csv') {

      $writer = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
      $writer->openToFile($path);

      if ($type_file == 'xlsx') {
        $writer->getCurrentSheet()->setName($doc_name);
      }

      if ($type_file == 'csv') {
        $writer->setFieldDelimiter(';');
      }

      // Prepare rows.
      $writer->addRow($data_headers);

      foreach ($data as $key => $item) {
        $writer->addRow([
          'name' => $item['name'],
          'identification' => $item['identification'],
          'order_number' => $item['order'],
          'creation_date' => $item['date'],
          'status' => $item['status'],
          'line_number' => $item['line_number'],
          'email' => $item['email'],
          'city' => $item['city'],
          'service_center' => $item['service_center'],
          'model' => $item['model'],
          'imei' => $item['imei'],
          're_entry' => $item['re_inside'],
          'description' => $item['description'],
          'accessories' => str_replace(",", "|", $item['accessories']),
        ]);
      }

      if ($writer->close()) {
      }
      else {
      }
    }

    // Prepare txt file.
    if ($type_file == 'txt') {
      $file = fopen($path, 'w');

      // Write data if export is in format txt.
      foreach ($data as $key => $value) {
        foreach ($data_headers as $header => $value_header) {
          fwrite($file, $value_header . "\r\n");
          if ($header == 'accessories') {
            $value[$header] = str_replace(",", "|", $value[$header]);
          }
          fwrite($file, (empty($value[$header])) ? t('No disponible') . "\r\n \r\n" : $value[$header] . "\r\n \r\n");
        }
        fwrite($file, "---------------------------------\r\n \r\n");
      }
    }

    if (fclose($file)) {
    }
    else {
    }

    $file_data = [
      'file_name' => $file_name,
    ];

    return $file_data;
  }

  /**
   * Implements buildData().
   *
   * @param mixed $data
   *   The client data.
   *
   * @return array
   *   The data to show.
   */
  public function buildData($data) {
    $response = [];
    foreach ($data as $data_key => $data_value) {
      $not_data_text = t('No disponible');
      $line_number_null = $order_null = $status_null = $imei_null = 0;
      if (method_exists($not_data_text, 'getUntranslatedString')) {
        $not_data_text = $not_data_text->getUntranslatedString();
      }

      // Get line number.
      $msisdn = $data_value->msisdn;
      $msisdn_without_format = 0;
      if (!isset($msisdn)) {
        $msisdn = $not_data_text;
        $line_number_null = 1;
      }
      else {
        $msisdn_without_format = $msisdn;
        $msisdn = $this->tboConfig->formatLine($msisdn);
      }

      // Get order.
      $ods = $data_value->ods;
      if (!isset($ods)) {
        $ods = $not_data_text;
        $order_null = 1;
      }

      // Get status.
      $status = $data_value->status;
      if (!isset($status)) {
        $status = $not_data_text;
        $status_null = 1;
      }

      // Get dateOrder.
      $dateOrder = $data_value->dateOrder;
      $date_order_timestamp = 0;
      if (isset($dateOrder)) {
        $dateOrder = $this->tboConfig->formatDate(strtotime($dateOrder));
        $date_order_timestamp = strtotime($dateOrder);
      }
      else {
        $dateOrder = $not_data_text;
      }

      // Get email.
      $email = $data_value->email;
      if (!isset($email)) {
        $email = $not_data_text;
      }

      // Get email.
      $city = $data_value->city;
      if (!isset($city)) {
        $city = $not_data_text;
      }

      // Get placeReceipt.
      $placeReceipt = $data_value->placeReceipt;
      if (!isset($placeReceipt)) {
        $placeReceipt = $not_data_text;
      }

      // Get model.
      $model = $data_value->model;
      if (!isset($model)) {
        $model = $not_data_text;
      }

      // Get imei.
      $imei = $data_value->imei;
      if (!isset($imei)) {
        $imei = $not_data_text;
        $imei_null = 1;
      }

      // Get accessories.
      $accessories = $data_value->accessories;
      if (!isset($accessories)) {
        $accessories = $not_data_text;
      }

      // Get reentry.
      $reentry = $data_value->reentry;
      if (!isset($reentry)) {
        $reentry = $not_data_text;
      }

      // Get description.
      $failure = $data_value->failure;
      if (!isset($failure)) {
        $failure = $not_data_text;
      }

      // Get name.
      $name = $data_value->name;
      if (!isset($name)) {
        $name = $not_data_text;
      }

      // Get Identification.
      $identification = $data_value->ci;
      if (!isset($identification)) {
        $identification = $not_data_text;
      }

      $response['fixed'][] = [
        'line_number' => $msisdn,
        'line_number_without_format' => $msisdn_without_format,
        'line_number_null' => $line_number_null,
        'order' => $ods,
        'order_null' => $order_null,
        'status' => $status,
        'status_null' => $status_null,
        'status_validate' => strtoupper($status),
        'date' => $dateOrder,
        'email' => $email,
        'city' => $city,
        'service_center' => $placeReceipt,
        'model' => $model,
        'imei' => $imei,
        'imei_null' => $imei_null,
        'accessories' => $accessories,
        're_inside' => $reentry,
        'description' => $failure,
        'timestamp' => $date_order_timestamp,
        'name' => $name,
        'identification' => $identification,
      ];
    }

    return $response;
  }

}
