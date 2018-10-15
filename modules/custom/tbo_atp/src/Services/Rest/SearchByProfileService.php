<?php

namespace Drupal\tbo_atp\Services\Rest;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class SearchByProfileService.
 *
 * @package Drupal\tbo_atp\Services\Rest
 */
class SearchByProfileService {

  protected $api;
  protected $tboConfig;
  protected $currentUser;
  protected $segment;

  /**
   * ServicePortfolioService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Config global tbo.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Object connect tigoUne.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api) {
    $this->tboConfig = $tboConfig;
    $this->api = $api;
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();
  }

  /**
   * Return data to rest.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   This current user.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The data response.
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

    $data = [];

    // Simulate behavior to CU47 for clientId.
    $params['query'] = [
      'docId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
    ];

    try {
      $dataClient = $this->api->getATPAccountsById($params);
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    $name_client = isset($dataClient->accountCollection[0]->accountName) ? $dataClient->accountCollection[0]->accountName : '';
    $data['client'] = $name_client;
    // Get accountId.
    $accountId = $_GET['p1'];

    if (!$accountId) {
      // Data of session.
      $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_atp');
      $accountId = $tempStore->get('tbo_atp_search_by_profile_temp_p1_' . $_SESSION['company']['nit']);

      if (!$accountId) {
        // Set var message.
        $e = new \Exception();
        $e->other_mesagge = t('Parameter Null');
        return new ResourceResponse(UtilMessage::getMessage($e));
      }
    }

    // Validate relation between clien and contract.
    $contract_collection = $dataClient->accountCollection;
    $size_collection = count($contract_collection);

    if ($size_collection == 0) {
      // Set var message.
      $e = new \Exception();
      $e->other_mesagge = t('Empty Collection');
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    $validate = FALSE;
    for ($i = 0; $i < $size_collection; $i++) {
      if ($contract_collection[$i]->accountId == $accountId) {
        $validate = TRUE;
        break;
      }
    }

    if (!$validate) {
      // Set var message.
      $e = new \Exception();
      $e->other_mesagge = t('Failed contract');
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    $data['contract'] = $accountId;

    // Consume getATPAccountProfilesByAccountId by accountId.
    $params = [
      'tokens' => [
        'accountId' => $accountId,
      ],
    ];

    try {
      $profilesByAccountId = $this->api->getATPAccountProfilesByAccountId($params);
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    foreach ($profilesByAccountId->profileCollection as $profile => $value) {
      if (strtoupper($value->status) == 'P') {
        // Consume getATPAccountProfileDetailsByProfileId.
        $params = [
          'tokens' => [
            'profile_id' => $value->id,
          ],
        ];

        try {
          $detailsProfile = $this->api->getATPAccountProfileDetailsByProfileId($params);
        }
        catch (\Exception $e) {
          // Return message in rest.
          return new ResourceResponse(UtilMessage::getMessage($e));
        }

        $detailsProfileCollections = [];

        // Add profile category.
        if (isset($detailsProfile->serviceCollection) && !empty($detailsProfile->serviceCollection)) {
          foreach ($detailsProfile->serviceCollection as $collection => $value_collection) {
            if (strtoupper($value_collection->status) == 'P') {
              $type = 'ATP ProfileCategory - ' . $value_collection->type;
              $temp = [
                'type' => t($type),
                'description' => $value_collection->description,
              ];
              $temp['type'] = str_replace('ATP ProfileCategory - ', '', $temp['type']);
              array_push($detailsProfileCollections, (array) $temp);
            }
          }
        }
        // Add profile.
        $data['profiles'][] = [
          'id_profile' => $value->id,
          'name_profile' => $value->name,
          'description_profile' => $value->description,
          'billingAccount_profile' => $value->billingAccount,
          'linesAmount_profile' => isset($detailsProfile->linesAmount) && $detailsProfile->linesAmount > 0 ? $detailsProfile->linesAmount : 0,
          'value_profile' => isset($detailsProfile->value) ? $this->tboConfig->formatCurrency($detailsProfile->value) : 0,
          'totalValue_profile' => isset($detailsProfile->totalValue) ? $this->tboConfig->formatCurrency($detailsProfile->totalValue) : 0,
          'serviceCollection_profile' => $detailsProfileCollections,
        ];
      }
    }

    if (isset($data['profiles'])) {
      // Save in session accountId temp.
      $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_atp');
      $tempStore->set('tbo_atp_search_by_profile_temp_p1_' . $_SESSION['company']['nit'], $accountId);
    }

    return new ResourceResponse($data);
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
      'event_type' => 'ATP',
      'description' => t('Usuario consulta por perfil el detalle de su plan corporativo ATP asociado al contrato @contractProfile', ['@contractProfile' => $params['contractProfile']]),
      'details' => t('Usuario @userName solicita consulta por perfil @nameProfile asociado al contrato @contractProfile',
        [
          '@userName' => $service->getName(),
          '@nameProfile' => $params['nameProfile'],
          '@contractProfile' => $params['contractProfile'],
        ]
      ),
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];
    // Save audit log.
    $service->insertGenericLog($data_log);

    // Set segment variable.
    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
    $segment_track = [
      'event' => 'TBO - Detalle Perfil cuenta ATP - Consulta',
      'userId' => $tigoId,
      'properties' => [
        'category' => 'Arma tu plan Business',
        'label' => $params['contractProfile'] . ' - movil',
        'site' => 'NEW',
      ],
    ];

    $this->segment->track($segment_track);

    return new ResourceResponse('Ok');

  }

}
