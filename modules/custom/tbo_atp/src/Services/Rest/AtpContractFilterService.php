<?php

namespace Drupal\tbo_atp\Services\Rest;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Drupal\user\Entity\User;
use Drupal\adf_segment\Services\AdfSegmentService;

/**
 * Class SearchByProfileService.
 *
 * @package Drupal\tbo_atp\Services\Rest
 */
class AtpContractFilterService {

  protected $api;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;

  /**
   * AtpContractFilterService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Servico de configuraciones de TBO.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Clase de acceso al api de servicios.
   * @param \Drupal\adf_segment\Services\AdfSegmentService $segment
   *   Clase de acceso al servicio de Segment.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api, AdfSegmentService $segment) {
    $this->tbo_config = $tboConfig;
    $this->api = $api;
    $segment->segmentPhpInit();
    $this->segment = $segment->getSegmentPhp();
  }

  /**
   * Petición GET.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Usuario actual.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Contracts, accountId and enterprise name.
   */
  public function get(AccountProxyInterface $currentUser) {

    // Denied cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $noDisponible = t('No disponible')->render();
    // Validate user permission.
    if (!$currentUser->hasPermission('access content')) {
      return new AccessDeniedHttpException();
    }

    if (!isset($_GET['log'])) {
      $params_name['query']['docId'] = $_SESSION['company']['nit'];

      try {
        $resp_service = $this->api->getATPAccountsById($params_name);
      }
      catch (\Exception $e) {
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      $name = $resp_service->accountCollection[0]->accountName;
      $response['name'] = (empty($name)) ? $noDisponible : $this->enterpriseName($name);

      foreach ($resp_service->accountCollection as $key => $value) {
        if (!empty($value->billingAccount)) {
          $response['data'][] = [
            'contract' => $value->billingAccount,
            'accountId' => $value->accountId,
          ];
        }
      }
    }
    else {
      $service = \Drupal::service('tbo_core.audit_log_service');

      $account = User::load($currentUser->id());
      $full_name = $account->get('full_name');
      $user = (empty($full_name) || isset($full_name)) ? $currentUser->getAccountName() : $full_name;

      $token_log = [
        '@user' => $user,
        '@contract' => $_GET['contract'],
      ];

      // Save audit log.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('ATP'),
        'description' => t('Usuario consulto resumen de sus planes corporativos (ATP) de sus servicios móviles'),
        'details' => t('Usuario @user consulta resumen de planes corporativos (ATP) del contrato @contract de los servicios móviles de su empresa', $token_log),
        'old_value' => $noDisponible,
        'new_value' => $noDisponible,
      ];
      $service->insertGenericLog($data_log);

      $response = 'OK';
    }

    if (empty($_SESSION['atp_services'])) {
      $contract = (!isset($_GET['contract'])) ? $resp_service->accountCollection[0]->billingAccount : $_GET['contract'];
      $accountId = (!isset($_GET['accountId'])) ? $resp_service->accountCollection[0]->accountId : $_GET['accountId'];
      $lines = $amount = $profiles = 0;

      $params_profiles['tokens'] = [
        'accountId' => $accountId,
      ];
      try {
        // First, we get the Account's Profiles.
        $resultProfiles = $this->api->getATPAccountProfilesByAccountId($params_profiles);
      }
      catch (\Exception $e) {
      }

      // Now we save an array of the Profiles with status "P".
      $filteredProfilesCollection = [];
      foreach ($resultProfiles->profileCollection as $key => $profile) {
        if (!empty($profile->billingAccount)) {
          if ($profile->status == 'P') {
            $filteredProfilesCollection[] = $profile;
          }
        }
      }

      $profiles = count($filteredProfilesCollection);
      foreach ($filteredProfilesCollection as $profile) {
        $params_profile_details['tokens'] = [
          'profileId' => $profile->id,
        ];
        try {
          $resultProfileDetails = $this->api->getATPAccountProfileDetailsByProfileId($params_profile_details);
        }
        catch (\Exception $e) {
        }

        // Double check Status.
        if ($resultProfileDetails->status == 'P') {
          // Summation of "totalValue" values.
          $amount += (empty($resultProfileDetails->totalValue)) ? 0 : intval($resultProfileDetails->totalValue);

          // Summation of "linesAmount" values.
          $lines += (empty($resultProfileDetails->linesAmount)) ? 0 : intval($resultProfileDetails->linesAmount);
        }
      }

      $tigoId = \Drupal::service('tigoid.repository')->getTigoId($currentUser->id());
      $this->segment->track([
        'event' => 'TBO - Visualizar cuenta ATP - Consulta',
        'userId' => $tigoId,
        'properties' => [
          'category' => 'Arma tu Plan Business',
          'label' => "$contract - $lines - $profiles - movil",
          'value' => $amount,
          'site' => 'NEW',
        ],
      ]);
    }

    return new ResourceResponse($response);
  }

  /**
   * EnterpriseName function.
   *
   * @param string $name
   *   Nombre de la empresa.
   *
   * @return string
   *   Formated enterprise name.
   */
  public function enterpriseName($name) {

    $name = ucwords(strtolower($name));

    $words = [
      ' SAS' => ' SAS',
      ' S.A.S' => ' SAS',
      ' SAS.' => ' SAS',
      ' LTDA' => ' LTDA',
      ' L.T.D.A' => ' LTDA',
      ' SA' => ' SA',
      ' S.A' => ' SA',
      ' SA.' => ' SA',
      ' SL' => ' SL',
      ' S.L' => ' SL',
    ];

    foreach ($words as $search => $word) {
      $name = str_ireplace($search, $word, $name);
    }

    return $name;
  }

  /**
   * Responds to POST requests, calls create method.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Usuario actual.
   * @param array|object $data
   *   Arreglo de valores obtenidos por el post.
   *
   * @return \Drupal\rest\ResourceResponse
   *   URI of file to download
   *
   * @throws \Box\Spout\Common\Exception\IOException
   * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
   * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
   */
  public function post(AccountProxyInterface $currentUser, $data) {

    // Denied cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Validate user permission.
    if (!$currentUser->hasPermission('access content')) {
      return new AccessDeniedHttpException();
    }
    if (array_key_exists('downloadType', $data)) {
      $company_nit = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
      $data_headers = [
        'company_nit' => t('NIT')->render(),
        'msisdn_line' => t('Número de línea')->render(),
        'type_line' => t('Tipo de plan')->render(),
        'profile_name' => t('Nombre del perfil')->render(),
        'profile_descripton' => t('Descripción del perfil')->render(),
        'account_father' => t('Cuenta padre')->render(),
        'account_child' => t('Cuenta sponsor')->render(),
        'category' => t('Nombre del servicio o categoría')->render(),
        'description_category' => t('Paquetes de servicios o recursos del plan')->render(),
        'value_category' => t('Valor total paquete de servicios')->render(),
      ];
      try {
        $data_export = $this->getAllAccountOrContracDetailByDocumentId($company_nit);
        $file_data = $this->getPreparedDataXslCsv($data_headers, $data_export, $company_nit, $data['type']);
        if ($data_export == FALSE) {
          $message = [
            'error' => TRUE,
            'code' => '400',
            'message' => 'El formato de exportación debe ser xlsx, csv o txt.',
            'message_error' => 'El formato de exportación debe ser xlsx, csv o txt.',
          ];
          return new ResourceResponse($message);
        }
        $this->saveSegmentTrack('TBO - Descargar Detalle Cuenta ATP - Tx', 'Exitoso - movil');
        return new ResourceResponse($file_data);
      }
      catch (\Exception $e) {
        $this->saveSegmentTrack('TBO - Descargar Detalle Cuenta ATP - Tx', 'Fallido - movil');
        $exception = UtilMessage::getMessage($e);
        return new ResourceResponse($exception);
      }
    }
    // Get cycle.
    $params_cycle['tokens'] = [
      'accountId' => $data['accountId'],
    ];

    try {
      $cycle = $this->api->getATPAccountProfilesByAccountId($params_cycle);
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    $cycle = ($cycle->profileCollection[0]->cycle == 0) ? 1 : $cycle->profileCollection[0]->cycle;
    $day = \Drupal::service('date.formatter')->format(time(), 'custom', 'd');

    if ($day < $cycle) {
      $time = strtotime('-1 month', time());
      $date = \Drupal::service('date.formatter')->format($time, 'custom', 'Y-m-');
    }
    else {
      $date = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-');
    }

    $date = $date . $cycle;
    $params = [
      'tokens' => [
        'contractId' => $data['contract'],
      ],
      'query' => [
        'cycle' => $date,
      ],
    ];

    // Get ATP invoice details.
    try {
      $response = $this->api->accountDetailsByCycle($params);
      $response = $response->payload;
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    // Starting whith file building.
    $type_file = isset($data['type']) ? $data['type'] : 'csv';

    // creación path del archivo.
    $dir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    $doc_name = "factura_" . $data['contract'];

    $date = \Drupal::service('date.formatter')->format(time(), 'custom', '_Y-m-d');
    $file_name = $doc_name . $date . '.' . $type_file;
    $path = $dir . '/' . $file_name;

    $get_type_format = \Drupal::config('tbo_general.settings')->get('region')['format_date'];
    $format = \Drupal::config('core.date_format.' . $get_type_format)->get('pattern');

    // Load charges and validate if have new charges.
    $service = \Drupal::service('tbo_atp.appointment_invoice_service');
    $service->loadNewCharges($response->billingDetailCollection);

    $data_headers = [
      'fatherAccount' => 'Cuenta padre',
      'childAccount' => 'Cuenta hija',
      'msisdn' => 'MSISDN',
      'value' => 'Valor',
      'typeCharge' => 'Cargo',
      'cycle' => 'ciclo',
    ];

    // Preparación del archivo excel.
    if ($type_file == 'xlsx' || $type_file == 'csv') {

      $writer = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
      $writer->openToFile($path);

      if ($type_file == 'xlsx') {
        $writer->getCurrentSheet()->setName($doc_name);
      }

      if ($type_file == 'csv') {
        $writer->setFieldDelimiter(';');
      }

      // Preparación de filas.
      $writer->addRow($data_headers);
      $noDisponible = t('No disponible')->render();
      foreach ($response->billingDetailCollection as $key => $item) {
        $date_to_format = new \DateTime($item->cycle);
        $writer->addRow([
          'fatherAccount' => (empty($response->fatherAccount)) ? $noDisponible : $response->fatherAccount,
          'childAccount' => (empty($item->childAccount)) ? $noDisponible : $item->childAccount,
          'msisdn' => (empty($item->msisdn)) ? $noDisponible : $item->msisdn,
          'value' => (empty($item->value)) ? $noDisponible : $item->value,
          'typeCharge' => $service->getTranslation($item->typeCharge),
          'cycle' => (empty($item->cycle)) ? $noDisponible : $date_to_format->format($format),
        ]);
      }

      if ($writer->close()) {
      }
      else {
      }
    }

    // Preparación archivo de texto.
    if ($type_file == 'txt') {
      $file = fopen($path, 'w');

      // Write data if export is in format txt or csv.
      foreach ($response->billingDetailCollection as $key => $value) {
        foreach ($data_headers as $header => $value_header) {
          fwrite($file, $value_header . "\r\n");
          \Drupal::logger('$header')->notice(print_r($value->$header, TRUE));
          $noDisponible = t('No disponible')->render();
          if ($header == 'fatherAccount') {
            fwrite($file, (empty($response->fatherAccount)) ? $noDisponible . "\r\n \r\n" : $response->fatherAccount . "\r\n \r\n");
          }
          elseif ($header == 'cycle') {
            $date_to_format = new \DateTime($value->$header);
            fwrite($file, (empty($value->cycle)) ? $noDisponible . "\r\n \r\n" : $date_to_format->format($format) . "\r\n \r\n");
          }
          elseif ($header == 'typeCharge') {
            fwrite($file, (empty($value->$header)) ? $noDisponible . "\r\n \r\n" : $service->getTranslation($value->$header) . "\r\n \r\n");
          }
          else {
            fwrite($file, (empty($value->$header)) ? $noDisponible . "\r\n \r\n" : $value->$header . "\r\n \r\n");
          }
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

    return new ResourceResponse($file_data);
  }

  /**
   * Retorna la información del archivo a exportar.
   *
   * @param array $data_headers
   *   Fila de las cabeceras de columnas.
   * @param array $data
   *   Arreglo de Datos.
   * @param string $company_nit
   *   DocumentId de la empresa.
   * @param string $type_file
   *   Extensión del fichero a generar.
   *
   * @return array|bool
   *   Arreglo de datos para la generación del fichero o false.
   *
   * @throws \Box\Spout\Common\Exception\IOException
   * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
   * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
   */
  private function getPreparedDataXslCsv(array $data_headers, array $data, $company_nit, $type_file = 'csv') {
    // Starting whith file building.
    // Creación path del archivo.
    $dir = \Drupal::service('stream_wrapper_manager')
      ->getViaUri('public://')
      ->realpath();

    $doc_name = "detail_account_" . $company_nit;

    $date = \Drupal::service('date.formatter')->format(time(), 'custom', '_Y-m-d');
    $file_name = $doc_name . $date . '.' . $type_file;
    $path = $dir . '/' . $file_name;
    if ($type_file == 'xlsx' || $type_file == 'csv') {
      $writer = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
      $writer->openToFile($path);
      if ($type_file == 'xlsx') {
        $writer->getCurrentSheet()->setName($doc_name);
      }

      if ($type_file == 'csv') {
        $writer->setFieldDelimiter(';');
      }

      // Preparación de filas.
      $writer->addRow($data_headers);

      foreach ($data as $key => $item) {
        $writer->addRow($item);
      }

      $writer->close();
    }
    elseif ($type_file == 'txt') {
      $file = fopen($path, 'w');

      // Write data if export is in format txt.
      foreach ($data as $key => $value) {
        foreach ($data_headers as $header => $value_header) {
          fwrite($file, $value_header . "\r\n");
          $noDisponible = t('No disponible')->render();
          fwrite($file, (empty($value[$header])) ? $noDisponible . "\r\n \r\n" : $value[$header] . "\r\n \r\n");
        }
        fwrite($file, "---------------------------------\r\n \r\n");
      }
      fclose($file);
    }
    else {
      return FALSE;
    }
    $file_data = [
      'file_name' => $file_name,
    ];

    return $file_data;
  }

  /**
   * Retorna la información a exportar para Detalles de Cuenta.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The data response.
   */
  public function getAllAccountOrContracDetailByDocumentId($company_nit) {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    $this->currentUser = \Drupal::currentUser();
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Simulate behavior to CU47 for clientId.
    $params['query'] = [
      'docId' => $company_nit,
    ];

    // Var to return data.
    $data = [];

    $dataClient = $this->api->getATPAccountsById($params);
    $name_client = isset($dataClient->accountCollection[0]->accountName) ? $dataClient->accountCollection[0]->accountName : '';
    $data['header']['client'] = $name_client;
    $all_data = [];
    for ($k = 0; $k < count($dataClient->accountCollection); $k++) {
      // Iterate over contract collection.
      $params = [
        'tokens' => [
          'accountId' => $dataClient->accountCollection[$k]->accountId,
        ],
      ];
      // Consume getATPAccountProfilesByAccountId by accountId.
      try {
        $profilesByAccountId = $this->api->getATPAccountProfilesByAccountId($params);
      }
      catch (\Exception $e) {
        continue;
      }

      // Validamos que tiene colecciones y que el estatus = P.
      if (isset($profilesByAccountId) && isset($profilesByAccountId->profileCollection) && !empty($profilesByAccountId->profileCollection)) {
        $profilesColection = $profilesByAccountId->profileCollection;
        // Se recorren los perfiles!
        for ($j = 0; $j < count($profilesColection); $j++) {
          if (strtoupper($profilesColection[$j]->status) == 'P') {
            // Por cada perfil obtengo la data!
            try {
              // Consume getATPAccountProfileDetailsByProfileId.
              $profile = $profilesColection[$j]->id;
              $params = [
                'tokens' => [
                  'profile_id' => $profile,
                ],
              ];
              $profile_data = $profilesColection[$j];
              $detailsProfile = $this->api->getATPAccountProfileDetailsByProfileId($params);
            }
            catch (\Exception $e) {
              // Return message in rest.
              continue;
            }
            if (strtoupper($detailsProfile->status) == 'P') {
              // Validate category in profile.
              $serviceCollection = $detailsProfile->serviceCollection;

              if (isset($detailsProfile->serviceCollection) && !empty($detailsProfile->serviceCollection) &&
                isset($detailsProfile->lineCollection) && !empty($detailsProfile->lineCollection)) {
                $allLineDataAdded = FALSE;
                for ($i = 0; $i < count($serviceCollection); $i++) {
                  if (strtoupper($serviceCollection[$i]->status) == 'P') {
                    $getType = 'ATP ProfileCategory - ' . $serviceCollection[$i]->type;
                    $type = t($getType);
                    $category = str_replace('ATP ProfileCategory - ', '', $type);
                    $description_category = $serviceCollection[$i]->description;
                    $value_category = $serviceCollection[$i]->taxPrice;
                    // Replace , to .
                    $category = str_replace(',', '.', $category);
                    $description_category = str_replace(',', '.', $description_category);
                    foreach ($detailsProfile->lineCollection as $collection => $value_collection) {
                      if (strtoupper($value_collection->status) == 'P') {
                        $all_data[] = $this->buildTupleWithLineAndServiceData($company_nit, $value_collection,
                          $profile_data, $detailsProfile, $category, $description_category, $value_category);
                      }
                    }
                  }
                  // Verifico que no se ha añadido la data de lineas.
                  elseif (!$allLineDataAdded) {
                    foreach ($detailsProfile->lineCollection as $collection => $value_collection) {
                      if (strtoupper($value_collection->status) == 'P') {
                        $all_data[] = $this->buildTupleWithLineDataOnly($all_data, $company_nit, $profile_data,
                          $detailsProfile, $value_collection);
                      }
                    }
                    $allLineDataAdded = TRUE;
                  }
                }
              }
              elseif ((isset($detailsProfile->serviceCollection) && !empty($detailsProfile->serviceCollection)) &&
                (!isset($detailsProfile->lineCollection) || empty($detailsProfile->lineCollection))) {
                for ($i = 0; $i < count($serviceCollection); $i++) {
                  if (strtoupper($serviceCollection[$i]->status) == 'P') {
                    $getType = 'ATP ProfileCategory - ' . $serviceCollection[$i]->type;
                    $type = t($getType);
                    $category = str_replace('ATP ProfileCategory - ', '', $type);
                    $description_category = $serviceCollection[$i]->description;
                    $value_category = $serviceCollection[$i]->taxPrice;
                    // Replace , to .
                    $category = str_replace(',', '.', $category);
                    $description_category = str_replace(',', '.', $description_category);

                    $all_data[] = $this->buildTupleWithServiceDataOnly($company_nit, $profile_data,
                      $detailsProfile, $category, $description_category, $value_category);
                  }
                }
              }
              elseif ((!isset($detailsProfile->serviceCollection) || empty($detailsProfile->serviceCollection)) &&
                (isset($detailsProfile->lineCollection) && !empty($detailsProfile->lineCollection))) {
                foreach ($detailsProfile->lineCollection as $collection => $value_collection) {
                  if (strtoupper($value_collection->status) == 'P') {
                    $all_data[] = $this->buildTupleWithLineDataOnly($all_data, $company_nit, $profile_data,
                      $detailsProfile, $value_collection);
                  }
                }
              }
            }
          }
        }
      }
    }
    // Save audit log.
    $data_log = [
      'description' => 'Usuario descargo información de todos sus contratos ATP',
      'success' => TRUE,
    ];
    $this->saveAuditLog($data_log['description'], $data_log['success']);
    return $all_data;
  }

  /**
   * Construye una tupla cuando solo están presentes los datos de serviceCollection.
   *
   * @param string $company_nit
   *   DocumentId de la empresa.
   * @param array|object $profile_data
   *   Datos del perfil.
   * @param object $detailsProfile
   *   Detalles del perfil.
   * @param string $category
   *   Nombre de la categoría.
   * @param string $description_category
   *   Descripción de la categoría.
   * @param string $value_category
   *   Valor de la categoría.
   *
   * @return array
   *   retorna una tupla en la estructura de un array.
   */
  private function buildTupleWithServiceDataOnly(
                                    $company_nit,
                                    $profile_data,
                                    $detailsProfile,
                                    $category,
                                    $description_category,
                                    $value_category) {
    // Add to export.
    return $this->buildTuple($company_nit, NULL, $profile_data, $detailsProfile,
      $category, $description_category, $value_category);
  }

  /**
   * Construye una tupla cuando solo con los datos de lineCollection de un perfil.
   *
   * @param string $company_nit
   *   DocumentId de la empresa.
   * @param array|object $profile_data
   *   Datos del perfil.
   * @param object $detailsProfile
   *   Detalles del perfil.
   * @param array $line
   *   Datos de la línea.
   *
   * @return array
   *   Retorna una tupla en forma de arreglo con los datos.
   */
  private function buildTupleWithLineDataOnly($company_nit, $profile_data, $detailsProfile, array $line) {
    // Add to export.
    return $this->buildTuple($company_nit, $line, $profile_data, $detailsProfile,
      NULL, NULL, NULL);
  }

  /**
   * Construye una tupla cuando se cuenta con todos los datos.
   *
   * @param string $company_nit
   *   DocumentId de la empresa.
   * @param \stdClass $value_collection
   *   Valor de la colección.
   * @param array|object $profile_data
   *   Datos del perfil.
   * @param object $detailsProfile
   *   Detalles del perfil.
   * @param string $category
   *   Nombre de la categoría.
   * @param string $description_category
   *   Descripción de la categoría.
   * @param string $value_category
   *   Valor de la categoría.
   *
   * @return array
   *   Tupla en forma de arreglo.
   */
  private function buildTupleWithLineAndServiceData(
                                  $company_nit,
                                  \stdClass $value_collection,
                                  $profile_data,
                                  $detailsProfile,
                                  $category,
                                  $description_category,
                                  $value_category) {

    $valueType = 'ATP AssociatedLines - ' . $value_collection->type;
    $temp['lines'] = [
      'type_line' => t($valueType),
      'msisdn_line' => $value_collection->msisdn,
    ];
    $temp['lines']['type_line'] = str_replace('ATP AssociatedLines - ', '', $temp['lines']['type_line']);

    // Add to export.
    return $this->buildTuple($company_nit, $temp['lines'], $profile_data, $detailsProfile,
      $category, $description_category, $value_category);

  }

  /**
   * Construye una tupla con todos los valores.
   *
   * @param string $company_nit
   *   DocumentId de la empresa.
   * @param array|null $line
   *   Datos de la linea en un array.
   * @param array|object $profile_data
   *   Datos del perfil.
   * @param object $detailsProfile
   *   Detalles del perfil.
   * @param string|null $category
   *   Nombre de la categoría.
   * @param string|null $description_category
   *   Descripción de la categoría.
   * @param string|null $value_category
   *   Valor de la categoría.
   *
   * @return array
   *   Tupla en forma de arreglo.
   */
  private function buildTuple($company_nit, $line, $profile_data, $detailsProfile, $category, $description_category, $value_category) {
    // No serviceCollection.
    $noDisponible = t('No disponible')->render();
    if ($category == NULL || $category == '') {
      $category = $noDisponible;
    }
    if ($description_category == NULL || $description_category == '') {
      $description_category = $noDisponible;
    }
    if ($value_category == NULL || $value_category == '') {
      $value_category = $noDisponible;
    }
    // No linesCollection.
    if ($line == NULL) {
      $line = ['msisdn_line' => $noDisponible, 'type_line' => $noDisponible];
    }
    if ($line['msisdn_line'] == '') {
      $line['msisdn_line'] = $noDisponible;
    }
    if ($line['type_line'] == '') {
      $line['type_line'] = $noDisponible;
    }
    $tuple = [
      'company_nit' => $company_nit,
      'msisdn_line' => $line['msisdn_line'],
      'type_line' => $line['type_line'],
      'profile_name' => $profile_data->name,
      'profile_descripton' => $profile_data->description,
      'account_father' => $detailsProfile->billingAccountFather,
      'account_child' => $detailsProfile->billingAccount,
      'category' => $category,
      'description_category' => $description_category,
      'value_category' => $value_category,
    ];
    return $tuple;
  }

  /**
   * Implements function to save audit log in associated lines.
   *
   * @param string $description
   *   The log description.
   * @param bool $success
   *   Representa si se registra un mensaje exitoso o no.
   */
  public function saveAuditLog($description, $success) {
    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    // Set detail.
    if ($success) {
      $set_detail = t('Usuario @nameUser descargo información de todos los contratos ATP de su empresa',
        [
          '@nameUser' => $service->getName(),
        ]
      );
    }
    else {
      $set_detail = t('Usuario @nameUser descargo información de todos los contratos ATP de su empresa',
        [
          '@nameUser' => $service->getName(),
        ]
      );
      $description = 'Usuario no pudo descargar información de todos sus contratos ATP';
    }
    $noDisponible = t('No disponible')->render();
    // Create array data_log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'ATP',
      'description' => t($description)->render(),
      'details' => $set_detail->render(),
      'old_value' => $noDisponible,
      'new_value' => $noDisponible,
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);
  }

  /**
   * Function to save segment track.
   *
   * @param string $event
   *   Track event.
   * @param string $label
   *   Track level.
   */
  public function saveSegmentTrack($event, $label) {
    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    $segment_track = [
      'event' => $event,
      'userId' => $tigoId,
      'properties' => [
        'category' => 'Arma tu plan Business',
        'label' => $label,
        'site' => 'NEW',
      ],
    ];
    $this->segment->track($segment_track);
  }

}
