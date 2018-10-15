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
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use \Drupal\system\Plugin\Archiver\Zip;

class FixedConsumptionDownloadRestLogic {
  
  protected $api;
  protected $currentUser;
  protected $tbo_config;
  
  /**
   * FixedConsumptionDownloadRestLogic constructor.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AccountProxyInterface $current_user) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->currentUser = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public function post($data) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $response = '';
    
    switch ($data['type_download']) {
      case 'daily':
        $response = 'daily';
        $type_file = $data['type_file'];
        $uid = \Drupal::currentUser()->id();
        $data_download = $data['data'];
        
        //creación path del archivo
        $dir = \Drupal::service('stream_wrapper_manager')
          ->getViaUri('public://')
          ->realpath();
        
        $date = date('Y-m-d H:i:s');
        $file_name = 'Reporte-consumo-fijo-diario-' . $uid . $date . '.' . $type_file;
        $path = $dir . '/' . $file_name;
        
        $data_headers = [
          'Fecha',
          'Hora',
          'Tipo de consumo',
          'Minutos',
          'Origen',
          'Destino',
        ];
        
        try {
          //preparación del archivo excel
          if ($type_file == 'xlsx' || $type_file == 'csv') {
            $data_export = $data;
            
            $header = '';
            
            $writer = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
            $writer->openToFile($path);
            
            if ($type_file == 'xlsx') {
              $header = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
              $writer->getCurrentSheet()->setName('Detalle de consumo data');
            }
            
            if ($type_file == 'csv') {
              $header = 'text/csv';
              $writer->setFieldDelimiter(';');
            }
            
            //Preparación de filas
            
            $group_rows = [];
            
            $writer->addRow($data_headers);
            
            foreach ($data_download as $item) {
              $writer->addRow($item);
            }
            
            if ($writer->close()) {
            }
            else {
            }
          }
          
          //preparación archivo de texto
          if ($type_file == 'txt') {
            $file = fopen($path, 'w');
            $header = 'text/plain';
            
            //Write data if export is in format txt or csv
            foreach ($data_download as $key => $value) {
              foreach ($data_headers as $header => $value_header) {
                fwrite($file, $value_header . "\r\n");
                
                fwrite($file, $data_download[$key][$header] . "\r\n \r\n");
              }
              fwrite($file, "---------------------------------\r\n \r\n");
            }
            
            if (fclose($file)) {
            }
            else {
            }
          }
          
          $url = substr($path, strpos($path, 'sites'), strlen($path));
          return ['url' => $url, 'path' => $path, 'file_name' => $file_name];
        }
        catch (Exception $exception) {
        }
        break;
      
      case 'month':
        $type_file = $data['type_file'];
        $uid = \Drupal::currentUser()->id();
        $data_download = $data['data'];
        
        //creación path del archivo
        $dir = \Drupal::service('stream_wrapper_manager')
          ->getViaUri('public://')
          ->realpath();
        
        $date = date('Y-m-d H:i:s');
        $zip_name = $dir.'/Reporte-consumo-fijo-diario'. $uid . $date.'.zip';
        $zip_name_1 = 'Reporte-consumo-fijo-diario'. $uid . $date.'.zip';
        $file_name_resume = 'Reporte-consumo-fijo-diario-resumen' . $uid . $date . '.' . $type_file;
        $path_resume = $dir . '/' . $file_name_resume;
        $file_name_detail = 'Reporte-consumo-fijo-diario-detalles' . $uid . $date . '.' . $type_file;
        $path_detail = $dir . '/' . $file_name_detail;
        
        $data_headers_resume = [
          'Fecha',
          'Minutos locales',
          'Minutos nacionales Une',
          'Minutos nacionales otros',
          'Minutos internacionales',
        ];
        
        $data_headers_details = [
          'Fecha',
          'Tipo de consumo',
          'Hora',
          'Minutos',
          'Origen',
          'Destino',
        ];
        
        try {
          //preparación del archivo excel
          if ($type_file == 'xlsx' || $type_file == 'csv') {
            $data_export_resume = $data['resume'];
            $data_export_detail = $data['detail'];
            
            $header = '';
            
            $writer_detail = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
            $writer_resume = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
            $writer_detail->openToFile($path_detail);
            $writer_resume->openToFile($path_resume);
            
            if ($type_file == 'xlsx') {
              $header = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
              $writer_resume->getCurrentSheet()
                ->setName('Detalle de consumo fijo');
              $writer_detail->getCurrentSheet()
                ->setName('Detalle de consumo fijo');
            }
            
            if ($type_file == 'csv') {
              $header = 'text/csv';
              $writer_resume->setFieldDelimiter(';');
              $writer_detail->setFieldDelimiter(';');
            }
            
            //Preparación de filas
            
            $group_rows = [];
            
            $writer_resume->addRow($data_headers_resume);
            $writer_detail->addRow($data_headers_details);
            
            foreach ($data_download['detail'] as $item) {
              $writer_detail->addRow($item);
            }
            
            foreach ($data_download['resume'] as $item) {
              $writer_resume->addRow($item);
            }
            
            if ($writer_detail->close()) {
            }
            else {
            }
            if ($writer_resume->close()) {
            }
            else {
            }
          }
          
          //preparación archivo de texto
          if ($type_file == 'txt') {
            $file_resume = fopen($path_resume, 'w');
            $file_detail = fopen($path_detail, 'w');
            $header = 'text/plain';
            
            //Write data if export is in format txt or csv
            foreach ($data_download['detail'] as $key => $value) {
              foreach ($data_headers_details as $header => $value_header) {
                fwrite($file_detail, $value_header . "\r\n");
                
                fwrite($file_detail, $data_download['detail'][$key][$header] . "\r\n \r\n");
              }
              fwrite($file_detail, "---------------------------------\r\n \r\n");
            }
            
            foreach ($data_download['resume'] as $key => $value) {
              foreach ($data_headers_resume as $header => $value_header) {
                fwrite($file_resume, $value_header . "\r\n");
                
                fwrite($file_resume, $data_download['resume'][$key][$header] . "\r\n \r\n");
              }
              fwrite($file_resume, "---------------------------------\r\n \r\n");
            }
            
            if (fclose($file_detail)) {
            }
            else {
            }
            
            if (fclose($file_resume)) {
            }
            else {
            }
          }
          
          $url_detail = substr($path_detail, strpos($path_detail, 'sites'), strlen($path_detail));
          $url_resume = substr($path_resume, strpos($path_resume, 'sites'), strlen($path_resume));
          $aux = rand();
          
          $zip = new \ZipArchive();
          $zip->open($zip_name, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
          $zip->addFile($path_detail, 'Detallado.'.$type_file);
          $zip->addFile($path_resume, 'Resumen.'.$type_file);
          $zip->close();
  
  
          $_SESSION['files_user'][$path_detail] = $path_detail;
          $_SESSION['files_user'][$path_resume] = $path_resume;
  
          return [
            'path' => $zip_name,
            'file_name' => $zip_name_1,
          ];
        }
        catch (Exception $exception) {
        }
        break;
    }
    return 'response';
  }
}
