<?php

namespace Drupal\tbo_lines\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MobileCallHistoryRestLogic {

  protected $api;

  protected $account;
  protected $segment;

  /**
   * MobileCallHistoryRestLogic constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboApiClientInterface $api, AccountInterface $account) {
    $this->api = $api;
    $this->account = $account;
    \Drupal::service('adf_segment')->segmentPhpInit();
    $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
  }

  /**
   *
   * @return ResourceResponse
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    if (!$this->account->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $filter_service = \Drupal::service('tbo_lines.call_history_filter_date');
    $service_date = $filter_service->getFilterDate();

    $end_date_request = \Drupal::request()->query->get('end_date');
    $init_date_request = \Drupal::request()->query->get('init_date');


    $dates['date_end'] = isset($end_date_request) ? $end_date_request : $service_date['end_date'];
    $dates['date_ini'] = isset($init_date_request) ? $init_date_request : $service_date['init_date'];

    $error_date = $filter_service->validateRangeDate($dates['date_ini'], $dates['date_end']);
    \Drupal::logger("Rango de error")->notice(print_r($error_date, TRUE));

    if ($error_date !== "") {
      drupal_set_message($error_date, 'error');
      return new ResourceResponse("Error en las fechas");
    }

    $params = [
      'query' => [
        'dateFrom' => $dates['date_ini'],
        'dateTill' => $dates['date_end'],
      ],
      'tokens' => [
        'account' => $_SESSION['serviceDetail']['address'],
      ],
      'headers' => [
        'sourceIp' => \Drupal::request()->server->get('SERVER_ADDR'),
      ],
    ];
    $get_type_format = \Drupal::config('tbo_general.settings')
      ->get('region')['format_date'];
    $format = \Drupal::config('core.date_format.' . $get_type_format)
      ->get('pattern');
    $get_type_hour = \Drupal::config('tbo_general.settings')
      ->get('region')['format_hour'];
    $format_hour = \Drupal::config('core.date_format.' . $get_type_hour)
      ->get('pattern');
    try {
      $response = $this->api->getCustomerCallsDetailsService($params);
      foreach ($response as $key => $value) {
        $get_date = new \DateTime($value->callDateTime);
        $resp[] = [
          'date' => $get_date->format($format),
          'hour' => $get_date->format($format_hour),
          'date_hour' => $get_date->format($format) . ' - ' . $get_date->format($format_hour),
          'number' => $this->setFormat($value->msisdn),
          'time_call' => $this->timeConvert($value->callDuration),
        ];
      }
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }
    return new ResourceResponse($resp);
  }

  /**
   * Convert time to format hh:mm:ss
   *
   * @param $time
   *
   * @return string
   */
  private function timeConvert($time) {
    $hour = floor($time / 3600);
    $min = floor(($time - ($hour * 3600)) / 60);
    $seg = $time - ($hour * 3600) - ($min * 60);
    if ($hour == 0) {
      $hour = "00";
    }
    if ($min == 0) {
      $min = "00";
    }
    if ($seg == 0) {
      $seg = "00";
    }
    if ($hour > 0 && $hour < 10) {
      $hour = "0" . $hour;
    }
    if ($min > 0 && $min < 10) {
      $min = "0" . $min;
    }
    if ($seg > 0 && $seg < 10) {
      $seg = "0" . $seg;
    }
    return $hour . ':' . $min . ":" . $seg;
  }

  public function post($data) {

    $type_file = isset ($data['type']) ? $data['type'] : 'txt';
    $type_download = isset($data['download']) ? $data['download'] : 'voz';
    
    try {
      $tigoId = \Drupal::service('tigoid.repository')
        ->getTigoId(\Drupal::currentUser()->id());
      
      if (isset($tigoId)){
        $this->segment->track([
          'event' => 'TBO - Descargar reporte consumos - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => 'Telefonía móvil - Voz - movil',
            'site' => 'NEW',
          ],
        ]);
      }
    }catch (\Exception $exception){
    }
    

    //creación path del archivo
    $dir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    $doc_name = "Reporte-consumo-$type_download";

    $date = date('Y-m-d');
    $file_name = $doc_name . $date . '.' . $type_file;
    $path = $dir . '/' . $file_name;

    $data_headers = $data['headers'];

    try {
      //preparación del archivo excel
      if ($type_file == 'xlsx' || $type_file == 'csv') {

        $writer = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
        $writer->openToFile($path);

        if ($type_file == 'xlsx') {
          $writer->getCurrentSheet()->setName('Detalle de consumo data');
        }

        if ($type_file == 'csv') {
          $writer->setFieldDelimiter(';');
        }

        //Preparación de filas
        $writer->addRow($data_headers);

        foreach ($data['data'] as $key => $item) {
          unset($data['data'][$key]['date_hour']);
          $writer->addRow($data['data'][$key]);
        }
        $writer->close();
      }
      //preparación archivo de texto
      if ($type_file == 'txt') {
        $file = fopen($path, 'w');

        //Write data if export is in format txt or csv
        foreach ($data['data'] as $key => $value) {
          foreach ($data_headers as $header => $value_header) {
            fwrite($file, $value_header . "\r\n");

            fwrite($file, $data['data'][$key][$header] . "\r\n");
          }
          fwrite($file, "---------------------------------\r\n");
        }
        fclose($file);
      }
      $file_data = [
        'file_name' => $file_name,
      ];
      return new ResourceResponse($file_data);
    } catch (\Exception $e) {
    }

  }

  /**
   * @param $val , Set a number format
   *
   * @return string
   */
  protected function setFormat($val) {
    if (strlen($val) == 10 && substr($val, 0, 1) == 3) {
      $val = '(' . substr($val, 0, 3) . ') ' . substr($val, 3, 3) . '-' . substr($val, 6, 4);
    }

    return $val;
  }
}
