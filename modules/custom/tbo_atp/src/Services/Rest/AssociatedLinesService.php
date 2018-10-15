<?php

namespace Drupal\tbo_atp\Services\Rest;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\adf_core\Base\BaseApiCache;

/**
 * Class SearchByProfileService.
 *
 * @package Drupal\tbo_atp\Services\Rest
 */
class AssociatedLinesService {

  protected $api;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;

  /**
   * AssociatedLinesService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Global config.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Object connect tigoUne.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api) {
    $this->tboConfig = $tboConfig;
    $this->api = $api;
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();
    $this->currentUser = \Drupal::currentUser();
  }

  /**
   * Return data to rest.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The data response.
   */
  public function get() {
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Simulate behavior to CU47 for contractId.
    $profile = $_GET['p1'];
    if (!$profile) {
      throw new AccessDeniedHttpException();
    }

    $contract = $_GET['p2'];
    if (!$contract) {
      throw new AccessDeniedHttpException();
    }

    // Simulate behavior to CU47 for clientId.
    $company_nit = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
    $params['query'] = [
      'docId' => $company_nit,
    ];

    // Var to return data.
    $data = [];

    $dataClient = $this->api->getATPAccountsById($params);
    $name_client = isset($dataClient->accountCollection[0]->accountName) ? $dataClient->accountCollection[0]->accountName : '';
    $data['header']['client'] = $name_client;

    // Consume getATPAccountProfilesByAccountId by accountId.
    $params = [
      'tokens' => [
        'accountId' => $contract,
      ],
    ];

    try {
      $profilesByAccountId = $this->api->getATPAccountProfilesByAccountId($params);
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    $validate = FALSE;
    $profile_data = [];
    // Validate contract and profile.
    if (isset($profilesByAccountId) && isset($profilesByAccountId->profileCollection) && !empty($profilesByAccountId->profileCollection)) {
      $profilesColection = $profilesByAccountId->profileCollection;
      for ($i = 0; $i < count($profilesColection); $i++) {
        if (strtoupper($profilesColection[$i]->status) == 'P') {
          if ($profilesColection[$i]->id == $profile) {
            $validate = TRUE;
            $profile_data = $profilesColection[$i];
            break;
          }
        }
      }
    }
    else {
      // Set var message.
      $e = new \Exception('getATPAccountProfilesByAccountId');
      $e->other_mesagge = 'getATPAccountProfilesByAccountId';
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    if (!$validate) {
      // Set var message.
      $e = new \Exception();
      $e->other_mesagge = t('GET Error');
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    // Save audit log.
    $data_log = [
      'description' => 'Usuario consulta por líneas asociadas al perfil',
      'detail' => 1,
    ];
    $this->saveAuditLog($data_log['description'], $data_log['detail'], $profile_data->name, $profile_data->billingAccount);
    // Save track segment. Se comenta porque se envia desde el twig en el boton.
    // $this->saveSegmentTrack('TBO - Ver Líneas Perfil cuenta ATP - consulta', $profile_data->billingAccount . ' contrato - movil');

    try {
      // Consume getATPAccountProfileDetailsByProfileId.
      $params = [
        'tokens' => [
          'profile_id' => $profile,
        ],
      ];

      $detailsProfile = $this->api->getATPAccountProfileDetailsByProfileId($params);
    }
    catch (\Exception $e) {
      // Return message in rest.
      if ($e->getCode() == 404) {
        $data['lines'] = [];
        return new ResourceResponse($data);
      }
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    if (strtoupper($detailsProfile->status) == 'P') {
      $category = '';
      $description_category = '';
      $value_category = '';

      // Validate category in profile.
      if (isset($detailsProfile->serviceCollection) && !empty($detailsProfile->serviceCollection)) {
        $serviceCollection = $detailsProfile->serviceCollection;
        for ($i = 0; $i < count($serviceCollection); $i++) {
          if (strtoupper($serviceCollection[$i]->status) == 'P') {
            $getType = 'ATP ProfileCategory - ' . $serviceCollection[$i]->type;
            $type = t($getType);

            if ($category == '') {
              $category .= str_replace('ATP ProfileCategory - ', '', $type);
            }
            else {
              $category .= '|' . str_replace('ATP ProfileCategory - ', '', $type);
            }

            if ($description_category == '') {
              $description_category .= $serviceCollection[$i]->description;
            }
            else {
              $description_category .= '|' . $serviceCollection[$i]->description;
            }

            if ($value_category == '') {
              $value_category .= $serviceCollection[$i]->taxPrice;
            }
            else {
              $value_category .= '|' . $serviceCollection[$i]->taxPrice;
            }

            // Replace , to .
            $category = str_replace(',', '.', $category);
            $description_category = str_replace(',', '.', $description_category);
          }
        }
      }

      $all_data = [];

      if (isset($detailsProfile->lineCollection) && !empty($detailsProfile->lineCollection)) {
        $temp = [];
        $counter = 0;

        foreach ($detailsProfile->lineCollection as $collection => $value_collection) {
          if (strtoupper($value_collection->status) == 'P') {
            $valueType = 'ATP AssociatedLines - ' . $value_collection->type;
            $temp['lines'][] = [
              'type_line' => t($valueType),
              'msisdn_line' => $value_collection->msisdn,
            ];
            end($temp['lines']);
            $key = key($temp['lines']);

            $temp['lines'][$key]['type_line'] = str_replace('ATP AssociatedLines - ', '', $temp['lines'][$key]['type_line']);

            // Add to export.
            $all_data[] = [
              $company_nit,
              $temp['lines'][$key]['msisdn_line'],
              $temp['lines'][$key]['type_line'],
              $profile_data->name,
              $profile_data->description,
              $detailsProfile->billingAccountFather,
              $detailsProfile->billingAccount,
              $category,
              $description_category,
              $value_category,
            ];

            // Increment counter.
            $counter++;

            // Add to data.
            if ($counter == 3) {
              $data['lines'][] = $temp['lines'];
              $temp = [];
              $counter = 0;
            }
          }
        }

        if (!empty($temp)) {
          $data['lines'][] = $temp['lines'];
        }

        $data['header']['id_profile'] = $profile;
      }
      else {
        $data['lines'] = [];
      }

      // Save data export in cache.
      $quantity_data_export = count($all_data);
      $size = 0;
      if ($quantity_data_export <= 1500) {
        BaseApiCache::set("tbo_atp", 'associated_lines_export_data', array_merge([$profile], [$contract]), $all_data, 180);
      }
      else {
        $resize = $quantity_data_export / 1500;
        $size = ceil($resize);
        $extract_array = array_chunk($all_data, 1500);
        for ($i = 0; $i < $size; $i++) {
          $key = $profile . $i;
          BaseApiCache::set("tbo_atp", 'associated_lines_export_data', array_merge([$key], [$contract]), $extract_array[$i], 180);
        }
      }
      // Save in session key to export.
      $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_atp');

      $data_export = [
        'size' => $size,
        'contract' => $profile_data->billingAccount,
        'profile' => $profile_data->name,
      ];
      $tempStore->set('tbo_atp_' . md5($profile . $contract), $data_export);

    }
    return new ResourceResponse($data);
  }

  /**
   * Implements function to save audit log in associated lines.
   *
   * @param string $description
   *   The log description.
   * @param string $detail
   *   The log detail.
   * @param int $profile
   *   The client profile.
   * @param int $contract
   *   The contract profile.
   */
  public function saveAuditLog($description, $detail, $profile, $contract) {
    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    // Set detail.
    if ($detail == 1) {
      $set_detail = t('Usuario @nameUser consulta por líneas asociadas al perfil @profile asociado al contrato @contract',
        [
          '@nameUser' => $service->getName(),
          '@profile' => $profile,
          '@contract' => $contract,
        ]
      );
    }
    else {
      $set_detail = t('Usuario @nameUser descarga reporte con el detalle de las líneas asociadas al perfil consultado @profile asociado al contrato @contract',
        [
          '@nameUser' => $service->getName(),
          '@profile' => $profile,
          '@contract' => $contract,
        ]
      );
    }

    // Create array data_log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'ATP',
      'description' => t($description),
      'details' => $set_detail,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
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
