<?php

namespace Drupal\tbo_lines\Services;

use Behat\Mink\Exception\Exception;
use Drupal\adf_core\Util\UtilArray;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Class ConsumptionDetailDataDownloadRestLogic.
 *
 * @package Drupal\tbo_lines\Services
 */
class ConsumptionDetailDataDownloadRestLogic {
  protected $currentUser;
  protected $tbo_config;
  
  
  /**
   * ConsumptionDetailDataDownloadRestLogic constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config) {
    $this->tbo_config = $tbo_config;
  }
  
  /**
   * @param AccountProxyInterface $currentUser
   * @return ResourceResponse
   */
  public function post(AccountProxyInterface $currentUser, $data) {
    $type_file = isset ($_GET['type']) ? $_GET['type'] : 'txt';
    
    //creación path del archivo
    $dir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();
    
    $date = date('Y-m-d H:i:s');
    $file_name = 'Reporte-consumo-datos' . $date . '.' . $type_file;
    $path = $dir . '/' . $file_name;
    
    $data_headers = [
      'Fecha',
      'Hora',
      'Consumo',
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
        
        foreach ($data as $item) {
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
        foreach ($data as $key => $value) {
          foreach ($data_headers as $header => $value_header) {
            fwrite($file, $value_header . "\r\n");
            
            fwrite($file, $data[$key][$header] . "\r\n \r\n");
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
    
  }
  
  public function get(){
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    
    $user = \Drupal::currentUser();
    
    $account = \Drupal\user\Entity\User::load($user->id());
    
    //Load fields account
    $account_fields = \Drupal::currentUser()->getAccount();
    if(isset($account_fields->full_name) && !empty($account_fields->full_name)){
      $name = $account_fields->full_name;
    }else{
      $name = \Drupal::currentUser()->getAccountName();
    }
    
    $data = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'event_type' => t('Servicios'),
      'description' => t('Usuario descarga reporte de detalle de consumo de datos'),
      'details' => t('Usuario @user descarga reporte @type_file de detalle consumo de datos de la línea @line asociada al contrato @contract',
        array(
          '@user' => $name,
          '@type_file' => $_GET['type_file'],
          '@line' => isset($_SESSION['serviceDetail']['address']) ? $_SESSION['serviceDetail']['address'] : 'No disponible',
          '@contract' => isset ($_SESSION['serviceDetail']['contractId']) ? $_SESSION['serviceDetail']['contractId'] : 'No disponible'
        )),
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    
    $service_log->insertGenericLog($data);
    
    return ["Se inserto el log"];
  }
  
}