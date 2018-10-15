<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\user\Entity\User;

/**
 * Class DownloadContractRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class DownloadContractRestService {
  protected $api;
  protected $currentUser;
  protected $tboConfig;
  protected $segment;

  /**
   * DownloadContractRestService constructor.
   *
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tboConfig
   *   Url config  of services.
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Url interface api.
   */
  public function __construct(TboConfigServiceInterface $tboConfig, TboApiClientInterface $api) {
    $this->tbo_config = $tboConfig;
    $this->api = $api;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Information CurrentUser of the services.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Url resource Response.
   */
  public function get(AccountProxyInterface $currentUser) {
    $request = \Drupal::request();
    $values = [
      'phone' => $request->query->get('phone'),
      'document' => $request->query->get('document'),
    ];

    $this->currentUser = $currentUser;
    $this->service_message = \Drupal::service('tbo_mail.send');

    // Data log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    $type_services = $request->query->get('type');
    $phone_number = $values['phone'];

    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Save audit log.
    $this->saveAuditLog($type_services, $phone_number);

    $params['tokens'] = [
      'docNumber' => $_SESSION['company']['nit'],
    ];

    $docNumber = \Drupal::service('tbo_entities_co.miscellany')
      ->getDv(intval($_SESSION['company']['nit']));

    $params2['tokens'] = [
      'docNumber' => $_SESSION['company']['nit'] . $docNumber,
    ];

    $params['query'] = [
      'movil' => $values['phone'],
      'segment' => 'CORPORATE',
    ];
    
    if (isset($values['document'])){
      $params['query']['document'] = $values['document'];
    }

    $params2['query'] = $params['query'];

    $params['no_exception'] = TRUE;
    $data = [];

    try {
      $response = $this->api->getDigitalDocumentByClientId($params);

      if (count($response) > 0) {
        $data = [
          'url' => $response->urlDocument,
        ];
      }
      else {
        $response2 = $this->api->getDigitalDocumentByClientId($params2);
        if (count($response2) > 0) {
          $data = [
            'url' => $response2->urlDocument,
          ];
        }
      }
    }
    catch (\Exception $e) {
      // Return message in rest.
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    return new ResourceResponse($data);
  }

  /**
   * {@inheritdoc}
   */
  public function post(AccountProxyInterface $currentUser, $values) {
    $service_message = \Drupal::service('tbo_mail.send');
    $service_type = $values['service_type'];
    $data_type = $values['data_type'];
    $segment = 'segmento';

    if ($values['type'] == 'normal') {

      // You must to implement the logic of your REST Resource here.
      // Use current user after pass authentication to validate access.
      if (!$currentUser->hasPermission('access content')) {
        throw new AccessDeniedHttpException();
      }

      $repository = \Drupal::service('tbo_account.repository');
      // Get id company.
      $cid = $repository->getCompanyToDocumentNumber($_SESSION['company']['nit']);
      // Load Users without roles $roles.
      $roles = ['admin_company'];
      // Load Users without roles $roles.
      $data = $repository->loadUserWithRolByCompany($cid, $roles);

      // Load current user.
      $user_load = User::load($currentUser->id());
      $current_user_name = $user_load->get('full_name')->getValue()['0']['value'];
      if ($current_user_name == '') {
        $current_user_name = $currentUser->getAccountName();
      }
      $user = [
        'name' => $current_user_name,
        'mail' => $currentUser->getEmail(),
      ];

      $document_number = $_SESSION['company']['nit'];
      $document_type = $_SESSION['company']['docType'];
      $enterprise_name = $_SESSION['company']['name'];

      $tokens_download = [];
      $tokens_download['enterprise'] = $enterprise_name;
      $tokens_download['enterprise_num'] = $document_number;
      $tokens_download['document'] = $document_type;
      $tokens_download['service_type'] = $service_type;
      $tokens_download['data_type'] = $data_type;
      $tokens_download['username'] = $user['name'];
      foreach ($data as $key => $value) {
        $full_name = $value->full_name;
        if ($full_name == '') {
          $full_name = $value->name;
        }
        $tokens_download['admin_enterprise'] = $full_name;
        $tokens_download['service_type'] = $service_type;
        $tokens_download['admin_mail'] = $value->mail;
        $tokens_download['admin_phone'] = $value->phone_number;
        $tokens_download['mail_to_send'] = $value->mail;
        // Send mail to admin_company.
        try {
          $send = $service_message->send_message($tokens_download, 'download_contract');
          $data = ['OK'];
        }
        catch (\Exception $e) {
          // Save audit log.

        }
      }

      // Save audit log.
      $this->saveAuditLog($service_type, $data_type, 2);
    }
    elseif ($values['type'] == 'log') {
      // Save audit log.
      $this->saveAuditLog($service_type, $data_type, 3);

      // Save segment track.
      $event = 'TBO - Solicitar Contrato - Tx';
      $category = 'Contratos';
      $label = $service_type;
      \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
    }

    return new ResourceResponse($data);
  }

  /**
   * Function to save audit log.
   *
   * @param string $service_type
   *    The service type.
   * @param int $data_type
   *    The data type to save "Contract or Line".
   * @param int $log_number
   *    Action to realize.
   */
  public function saveAuditLog($service_type = '', $data_type = 0, $log_number = 1) {
    // Data log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();

    $event_type = 'Contrato';
    $description = t('Usuario consulta contrato digital único asociado de servicio @type_service', [
      '@type_service' => $service_type,
    ]);
    $detail = t('Usuario @userName consulta Documentos y contratos del @contract_line del servicio @type_service',
      [
        '@userName' => $name,
        '@contract_line' => $data_type,
        '@type_service' => $service_type,
      ]
    );

    if ($log_number == 2) {
      $description = t("Usuario descarga contrato digital único asociado de servicio @type_service", [
        '@type_service' => $service_type,
      ]);
      $detail = t('Usuario @userName descarga contrato del @contract_line del servicio @type_service',
        [
          '@userName' => $name,
          '@contract_line' => $data_type,
          '@type_service' => $service_type,
        ]
      );
    }

    if ($log_number == 3) {
      $description = t("Usuario solicita envió físico de contrato único asociado de servicio @type_service", [
        '@type_service' => $service_type,
      ]);
      $detail = t('Usuario @userName solicita envió físico de contrato del @contract_line del servicio @type_service',
        [
          '@userName' => $name,
          '@contract_line' => $data_type,
          '@type_service' => $service_type,
        ]
      );
    }

    // Create Audit log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => $event_type,
      'description' => $description,
      'details' => $detail,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $service_log->insertGenericLog($data_log);
  }

}
