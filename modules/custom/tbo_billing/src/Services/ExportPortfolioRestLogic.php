<?php

namespace Drupal\tbo_billing\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_mail\SendMessageInterface;
use Drupal\rest\ResourceResponse;
use Drupal\adf_core\Util\UtilMessage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Class ExportPortfolioRestLogic.
 *
 * @package Drupal\tbo_billing\Services
 */
class ExportPortfolioRestLogic {
  protected $api;
  protected $send;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;
  protected $message;
  protected $type;
  protected $typeFile;
  protected $dateFormat;

  /**
   * ExportPortfolioRestLogic constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Config.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Api.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user.
   * @param \Drupal\tbo_mail\SendMessageInterface $sendMessage
   *   Send message.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api, AccountInterface $currentUser, SendMessageInterface $sendMessage) {
    $this->api = $api;
    $this->send = $sendMessage;
    $this->tboConfig = $tboConfig;
    $this->currentUser = $currentUser;
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();

    // Get format date.
    $settings = \Drupal::config('tbo_general.settings');
    $format = \Drupal::config('core.date_format.' . $settings->get('region')['format_date']);
    $this->dateFormat = !empty($format->get('pattern')) ? $format->get('pattern') : 'Y-m-d';

    // Get type file.
    $settings = \Drupal::config('tbo_billing.bill_payment_settings');
    $this->typeFile = !empty($settings->get('portfolio')['download']['typeFile']) ? $settings->get('portfolio')['download']['typeFile'] : 'txt';

    // Init all messages.
    $this->type = 'fijo - movil';
    $this->message = [
      'CON' => (string) t('Control'),
      'PRE' => (string) t('Prepago'),
      'POS' => (string) t('Pospago'),
      'ATP' => (string) t('Arma Tu Plan'),
      'RTR' => (string) t('RTR Pospago'),
      'StatusActive' => (string) t('Activo'),
      'StatusDescriptionActive' => (string) t('Servicio activo'),
      'StatusInactive' => (string) t('Inactivo'),
      'StatusDescriptionInactive' => (string) t('Sin servicio'),
      'StatusSuspended' => (string) t('Suspendido'),
      'StatusDescriptionSuspended' => (string) t('Suspendido por solicitud del cliente'),
      'StatusLimit' => (string) t('Suspendido'),
      'StatusDescriptionLimit' => (string) t('Suspendido limite de Consumo'),
      'StatusIndebtedness' => (string) t('Suspendido'),
      'StatusDescriptionIndebtedness' => (string) t('Suspendido por Deuda'),
      'StatusFraud' => (string) t('Suspendido'),
      'StatusDescriptionFraud' => (string) t('Suspendido por fraude'),
      'StatusLoss' => (string) t('Suspendido'),
      'StatusDescriptionLoss' => (string) t('Suspendido por perdida'),
      'StatusTemporal' => (string) t('Suspendido'),
      'StatusDescriptionTemporal' => (string) t('Suspendido temporal por cliente'),
      'FileFolder' => !empty($settings->get('portfolio')['download']['folder']) ? (string) t($settings->get('portfolio')['download']['folder']) : (string) t('Portafolio'),
      'FileNameMobile' => !empty($settings->get('portfolio')['download']['fileMobile']) ? (string) t($settings->get('portfolio')['download']['fileMobile']) : (string) t('Portafolio - movil'),
      'FileNameFixed' => !empty($settings->get('portfolio')['download']['fileFixed']) ? (string) t($settings->get('portfolio')['download']['fileFixed']) : (string) t('Portafolio - fijo'),
      'FileNameZip' => !empty($settings->get('portfolio')['download']['fileZip']) ? (string) t($settings->get('portfolio')['download']['fileZip']) : (string) t('Portafolio'),
      'MovileCategory' => !empty($settings->get('portfolio')['download']['movileCategory']) ? (string) t($settings->get('portfolio')['download']['movileCategory']) : (string) t('Telefonía móvil'),
      'Portfolio' => (string) t('portafolio'),
      'EventType' => (string) t('Servicios'),
      'Description' => (string) t('Descarga de Portafolio exitosa'),
      'DescriptionError' => (string) t('Descarga de Portafolio no exitosa'),
      'detailsSuccess' => (string) t('Usuario @user descargó el Portafolio de Servicios, en el formato de archivo @typeFile.'),
      'detailsErrorAdmin' => (string) t('Usuario @user no pudo descargar el Portafolio de Servicios. El error retornado por el servicio web a consumir fue @code_error y descripción "@message_error".'),
      'detailsError' => (string) t('Usuario @user no pudo descargar el Portafolio de Servicios.'),
      'NoDisponible' => (string) t('No disponible'),
      'MessageError' => (string) t('Ha ocurrido un error.<br>La solicitud no pudo procesarse correctamente, por favor inténtelo más tarde.'),
    ];
  }

  /**
   * Implements get().
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function get() {
    // Prevent caching.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Validate permission.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    return new ResourceResponse([]);
  }

  /**
   * Implements post().
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function post() {
    // Prevent caching.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Validate permission.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      return $this->exportPortfolio();
    }
    catch (\Exception $error) {
      \Drupal::logger('Export portfolio')->error($error->getMessage() . '<br>' . $error->getTraceAsString());
      return $this->responseError($error);
    }
  }

  /**
   * Implements exportPortfolio().
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function exportPortfolio() {
    // Initialize.
    $portfolio = [];

    // Get portfolio of mobile services.
    $this->type = 'movil';
    $portfolio['mobile'] = $this->getPortfolioMobile($_SESSION['company']['nit'], $_SESSION['company']['docType']);
    $existMobile = count($portfolio['mobile']) > 0;

    // Get portfolio of fixed services.
    $this->type = 'fijo';
    $portfolio['fixed'] = $this->getPortfolioFixed($_SESSION['company']['nit'], $_SESSION['company']['docType']);
    $existFixed = count($portfolio['fixed']) > 0;

    if ($existMobile && $existFixed) {
      $this->type = 'fijo - movil';
    }
    elseif ($existMobile || $existFixed) {
      if ($existMobile) {
        $this->type = 'movil';
      }
      if ($existFixed) {
        $this->type = 'fijo';
      }
    }
    else {
      return $this->responseSuccess('', '');
    }

    // Get path.
    $date = new \DateTime();
    $date = $this->getDateformatted($date->getTimestamp());
    $date = str_replace('/', '-', $date);
    $date = str_replace(':', '_', $date);
    $path = $this->getPath();
    $this->message['FileFolder'] = $this->message['FileFolder'] . ' ' . $date;
    $fileNameMobile = $this->message['FileNameMobile'] . ' ' . $date . '.' . $this->typeFile;
    $fileNameFixed = $this->message['FileNameFixed'] . ' ' . $date . '.' . $this->typeFile;
    $fileNameZip = $this->message['FileNameZip'] . ' ' . $date . '.zip';

    // Create file portfolio of mobile services.
    if ($existMobile) {
      $headers = [
        (string) t('Categoría_Servicio'),
        (string) t('Tipo_Documento'),
        (string) t('Documento'),
        (string) t('Número_Línea'),
        (string) t('Número_SIM'),
        (string) t('Cuenta_Facturación'),
        (string) t('Ciclo_Facturación'),
        (string) t('Estado_Servicio'),
        (string) t('Fecha_Activación'),
        (string) t('Tipo_Plan'),
        (string) t('Descripción_Plan'),
      ];

      $this->createFile($headers, $portfolio['mobile'], $path . '/' . $fileNameMobile);
    }

    // Create file portfolio of fixed services.
    if ($existFixed) {
      $headers = [
        (string) t('Categoría_Servicio'),
        (string) t('Tipo_Documento'),
        (string) t('Documento'),
        (string) t('Identificador_Servicio'),
        (string) t('Número_Telefónico'),
        (string) t('Tecnología_Acceso'),
        (string) t('Descripción_Plan'),
        (string) t('Estado_Servicio'),
        (string) t('Fecha_Activación'),
        (string) t('Contrato_Facturación'),
        (string) t('Ciudad'),
        (string) t('Departamento'),
        (string) t('Dirección_Instalación'),
        (string) t('Dirección_Facturación'),
        (string) t('Tipo_Equipo'),
        (string) t('Marca_Equipo'),
        (string) t('Modelo_Equipo'),
        (string) t('Modelo_Equipo'),
        (string) t('Serial_Equipo'),
        (string) t('MAC_Equipo'),
      ];

      $this->createFile($headers, $portfolio['fixed'], $path . '/' . $fileNameFixed);
    }

    // Create file zip.
    if ($existMobile || $existFixed) {
      $this->createZip($existMobile, $existFixed, $path, $fileNameZip, $fileNameMobile, $fileNameFixed);
    }

    return $this->responseSuccess($path, $fileNameZip);
  }

  /**
   * Implements responseSuccess().
   *
   * @param string $path
   *   Document.
   * @param string $fileNameZip
   *   Document type.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function responseSuccess($path, $fileNameZip) {
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $path = substr($path, strpos($path, 'portfolio'), strlen($path));
    $path = str_replace('/', '&', $path);

    $response = [
      'error' => FALSE,
      'path' => $path,
      'file' => $fileNameZip,
    ];

    // Token log.
    $token = [
      '@user' => $service->getName(),
      '@typeFile' => $this->typeFile,
    ];

    // Log on fail.
    $log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => $this->message['EventType'],
      'description' => $this->message['Description'],
      'details' => t('Usuario @user descargó el Portafolio de Servicios, en el formato de archivo @typeFile.', $token),
      'old_value' => $this->message['NoDisponible'],
      'new_value' => $this->message['NoDisponible'],
    ];

    if (!empty($path) && !empty($fileNameZip)) {
      // Save audit log.
      $service->insertGenericLog($log);

      // Send segment track.
      $this->sendSegmentTrack('Exitoso');
    }

    return new ResourceResponse($response);
  }

  /**
   * Implements responseError().
   *
   * @param mixed $error
   *   Exception.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function responseError($error) {
    $roles = $this->currentUser->getRoles();
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    $response = [
      'error' => TRUE,
      'message_error' => $this->message['MessageError'],
    ];

    // Token log.
    $token = [
      '@user' => $service->getName(),
    ];

    // Add information to log if
    // the user has the role super_admin or tigo_admin.
    if (is_array($roles) && (in_array('super_admin', $roles) || in_array('admin_company', $roles) || in_array('tigo_admin', $roles))) {
      $error = UtilMessage::getMessage($error);
      $token['@code_error'] = $error['code'] != 0 ? $error['code'] : 400;
      $token['@message_error'] = !empty($error['message_error']) ? $error['message_error'] : 'bad request';
      $logDetail = t('Usuario @user no pudo descargar el Portafolio de Servicios. El error retornado por el servicio web a consumir fue @code_error y descripción "@message_error".', $token);
    }
    else {
      $logDetail = t('Usuario @user no pudo descargar el Portafolio de Servicios.', $token);
    }

    // Log on fail.
    $log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => $this->message['EventType'],
      'description' => $this->message['DescriptionError'],
      'details' => $logDetail,
      'old_value' => $this->message['NoDisponible'],
      'new_value' => $this->message['NoDisponible'],
    ];

    // Save audit log.
    $service->insertGenericLog($log);

    // Send segment track.
    $this->sendSegmentTrack('Fallido');

    return new ResourceResponse($response);
  }

  /**
   * Implements sendSegmentTrack().
   *
   * @param string $status
   *   Status.
   */
  public function sendSegmentTrack($status) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());

    if (isset($tigoId)) {
      try {
        $segment_track = [
          'event' => 'TBO - Exportar portafolio - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => $status . ' - ' . $this->type,
          ],
        ];

        $this->segment->track($segment_track);
      }
      catch (\Exception $error) {
      }
    }
  }

  /**
   * Implements getPortfolioMobile().
   *
   * @param string $document
   *   Document.
   * @param string $documentType
   *   Document type.
   *
   * @return array
   *   Resultado de la solicitud.
   */
  public function getPortfolioMobile($document, $documentType) {
    $params = [];
    $cantidad = 20;
    $portfolioFixed = [];

    // Parameters for mobile services.
    $params['query'] = [
      'id' => $document,
      'idType' => $documentType,
      'businessUnit' => 'B2B',
      'offset' => 1,
      'limit' => 20,
    ];

    while ($cantidad == $params['query']['limit']) {
      $cantidad = 0;
      $lines = [];

      try {
        $lines = $this->api->GetLineDetailsbyDocumentId($params);
      }
      catch (\Exception $error) {
        $messageError = json_decode($error->getMessage());

        if ($messageError->error->statusCode != 404) {
          throw new \Exception();
        }
      }

      if (isset($lines->lineCollection)) {
        foreach ($lines->lineCollection as $line) {
          $statusDescription = '';
          $status = $line->status;
          $this->getStatus($status, $statusDescription);

          $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $line->installationDate);
          $timestamp = $datetime->getTimestamp();

          $portfolioMobile[] = [
            $this->message['MovileCategory'],
            $documentType,
            $document,
            $this->getValidValue($line->msisdn),
            $this->getValidValue($line->imsi),
            $this->getValidValue($line->csn),
            $this->getValidValue($line->cycle),
            // $status,.
            $statusDescription,
            $this->getDateformatted($timestamp),
            $this->getTypePlan($line->plan->planType),
            $this->getValidValue($line->plan->planDescription),
          ];

          $cantidad++;
        }
      }

      $params['query']['offset'] = $params['query']['offset'] + $cantidad;
    }

    return $portfolioMobile;
  }

  /**
   * Implements getPortfolioFixed().
   *
   * @param string $document
   *   Document.
   * @param string $documentType
   *   Document type.
   *
   * @return array
   *   Resultado de la solicitud.
   */
  public function getPortfolioFixed($document, $documentType) {
    $params = [];
    $products = [];
    $portfolioFixed = [];

    // Parameters for fixed services.
    $params['tokens'] = [
      'documentNumber' => $document,
      'documentType' => strtoupper($documentType) == 'NIT' ? 'NT' : $documentType,
    ];

    try {
      $products = $this->api->getByAccountUsingCustomer($params);
    }
    catch (\Exception $error) {
      $messageError = json_decode($error->getMessage());

      if ($messageError->error->statusCode != 403) {
        throw new \Exception();
      }
    }

    foreach ($products as $product) {
      foreach ($product->offeringList as $productItem) {
        $statusDescription = '';
        $status = $productItem->status;
        $this->getStatus($status, $statusDescription);

        $datetime = \DateTime::createFromFormat('Y-m-d', $productItem->fromDate);
        $timestamp = $datetime->getTimestamp();

        if (count($productItem->devices) == 0) {
          $portfolioFixed[] = [
            $this->getValidValue($product->productName),
            $documentType,
            $document,
            $this->getValidValue($productItem->subscriptionNumber),
            $this->getValidValue($productItem->measuringElement),
            $this->getValidValue($productItem->mediaType),
            $this->getValidValue($productItem->offeringName),
            // $status,.
            $statusDescription,
            $this->getDateformatted($timestamp),
            $this->getValidValue($productItem->Contract->contractId),
            '',
            '',
            $this->getValidValue($productItem->Contract->streetAddress),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
          ];
        }
        else {
          foreach ($productItem->devices as $device) {
            $portfolioFixed[] = [
              $this->getValidValue($product->productName),
              $documentType,
              $document,
              $this->getValidValue($productItem->subscriptionNumber),
              $this->getValidValue($productItem->measuringElement),
              $this->getValidValue($productItem->mediaType),
              $this->getValidValue($productItem->offeringName),
              // $status,.
              $statusDescription,
              $this->getDateformatted($timestamp),
              $this->getValidValue($productItem->Contract->contractId),
              '',
              '',
              $this->getValidValue($productItem->Contract->streetAddress),
              '',
              $this->getValidValue($device->type),
              $this->getValidValue($device->manufacturer),
              $this->getValidValue($device->modelId),
              $this->getValidValue($device->modelName),
              $this->getValidValue($device->serialNumber),
              $this->getValidValue($device->extendedUniqueIdentifier),
            ];
          }
        }
      }
    }

    return $portfolioFixed;
  }

  /**
   * Implements getValidValue().
   *
   * @param mixed $value
   *   Value.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function getValidValue($value) {
    return (!empty($value)) ? $value : '';
  }

  /**
   * Implements getDateformatted().
   *
   * @param mixed $value
   *   Value.
   *
   * @return string
   *   Resultado de la solicitud.
   */
  public function getDateformatted($value) {
    return format_date($value, 'custom', $this->dateFormat);
  }

  /**
   * Implements getTypePlan().
   *
   * @param string $value
   *   Value.
   *
   * @return string
   *   Resultado de la solicitud.
   */
  public function getTypePlan($value) {
    switch ($value) {
      case 'CON':
        $value = $this->message['CON'];
        break;

      case 'PRE':
        $value = $this->message['PRE'];
        break;

      case 'POS':
        $value = $this->message['POS'];
        break;

      case 'ATP':
        $value = $this->message['ATP'];
        break;

      case 'RTR':
        $value = $this->message['RTR'];
        break;
    }

    return $value;
  }

  /**
   * Implements getStatus().
   *
   * @param string $status
   *   Status.
   * @param string $statusDescription
   *   Status description.
   */
  public function getStatus(&$status, &$statusDescription) {
    switch (strtolower($status)) {
      case 'active':
        $status = $this->message['StatusActive'];
        $statusDescription = $this->message['StatusDescriptionActive'];
        break;

      case 'inactive':
        $status = $this->message['StatusInactive'];
        $statusDescription = $this->message['StatusDescriptionInactive'];
        break;

      case 'suspended for client request':
        $status = $this->message['StatusSuspended'];
        $statusDescription = $this->message['StatusDescriptionSuspended'];
        break;

      case 'suspended limit consumption':
        $status = $this->message['StatusLimit'];
        $statusDescription = $this->message['StatusDescriptionLimit'];
        break;

      case 'indebtedness':
        $status = $this->message['StatusIndebtedness'];
        $statusDescription = $this->message['StatusDescriptionIndebtedness'];
        break;

      case 'fraud':
        $status = $this->message['StatusFraud'];
        $statusDescription = $this->message['StatusDescriptionFraud'];
        break;

      case 'theft_loss':
        $status = $this->message['StatusLoss'];
        $statusDescription = $this->message['StatusDescriptionLoss'];
        break;

      case 'temporal_suspended_client':
        $status = $this->message['StatusTemporal'];
        $statusDescription = $this->message['StatusDescriptionTemporal'];
        break;
    }
  }

  /**
   * Implements getPath().
   *
   * @return string
   *   Path.
   */
  public function getPath() {
    $directory = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    $path = $directory . '/portfolio/' . md5(microtime());
    file_prepare_directory($path, FILE_CREATE_DIRECTORY);

    return $path;
  }

  /**
   * Implements createFile().
   *
   * @param mixed $headers
   *   Headers.
   * @param mixed $data
   *   Data.
   * @param string $fileName
   *   File name.
   */
  public function createFile($headers, $data, $fileName) {
    switch ($this->typeFile) {
      case 'xlsx':
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($fileName);
        $writer->addRow($headers);

        foreach ($data as $item) {
          $writer->addRow($item);
        }

        $writer->close();
        break;

      case 'csv':
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToFile($fileName);
        $writer->setFieldDelimiter(';');
        $writer->addRow($headers);

        foreach ($data as $item) {
          $writer->addRow($item);
        }

        $writer->close();
        break;

      case 'txt':
        $file = fopen($fileName, 'w');

        foreach ($data as $values) {
          foreach ($values as $key => $value) {
            fwrite($file, $headers[$key] . ': ' . $values[$key] . "\r\n");
          }

          fwrite($file, "--------------------------------------------------\r\n");
        }

        fclose($file);
        break;
    }
  }

  /**
   * Implements createZip().
   *
   * @param bool $existMobile
   *   Exist mobile.
   * @param bool $existFixed
   *   Exist fixed.
   * @param string $path
   *   Path.
   * @param string $fileNameZip
   *   File name.
   * @param string $fileNameMobile
   *   File name.
   * @param string $fileNameFixed
   *   File name.
   */
  public function createZip($existMobile, $existFixed, $path, $fileNameZip, $fileNameMobile, $fileNameFixed) {
    $zip = new \ZipArchive();
    $zip->open($path . '/' . $fileNameZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    if ($existMobile) {
      $zip->addFile($path . '/' . $fileNameMobile, $this->message['FileFolder'] . '/' . $fileNameMobile);
    }

    if ($existFixed) {
      $zip->addFile($path . '/' . $fileNameFixed, $this->message['FileFolder'] . '/' . $fileNameFixed);
    }

    $zip->close();
  }

}
