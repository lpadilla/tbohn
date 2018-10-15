<?php

namespace Drupal\tbo_services\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class NotificationRestService.
 *
 * @package Drupal\tbo_services\Services\Rest
 */
class NotificationRestService {

  protected $api;
  protected $currentUser;
  protected $segment;

  /**
   * NotificationRestService constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   The api interface.
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
    $service = \Drupal::service('adf_segment');
    $service->segmentPhpInit();
    $this->segment = $service->getSegmentPhp();
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

    // Get user mail.
    $mail = $currentUser->getEmail();

    if ($params['notification_id'] == 0 && $params['send_verified'] == 0) {
      // Save audit log alerts.
      $this->saveAuditLog(1);
      return new ResourceResponse("OK");
    }

    // Validate notification_id and user.
    // Get data.
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_notifications');
    $notifications_allowed = $tempStore->get('tbo_notifications_allowed_' . $currentUser->id());

    if (empty($notifications_allowed)) {
      throw new AccessDeniedHttpException();
    }

    $notification_id = $params['notification_id'];
    if (empty($notification_id)) {
      throw new AccessDeniedHttpException();
    }

    $notification_id = (int) $notification_id;
    if (!in_array($notification_id, $notifications_allowed)) {
      throw new AccessDeniedHttpException();
    }

    // Get tool service.
    $notification_service = \Drupal::service('tbo_services.tools_notifications');
    $uid = $currentUser->id();

    // Get repository.
    $repository = \Drupal::service('tbo_services.tbo_services_repository');
    // Validate relation and create.
    $exist = $repository->getNotificationDetail($notification_id, $uid);

    // Validate Action.
    if ($params['send_verified'] == 1) {
      // Send verification.
      $jsonBody = [
        'email' => $mail,
      ];
      $jsonBody = json_encode($jsonBody);
      $params = [
        'query' => [
          'email_type' => 'verification_email',
        ],
        'body' => $jsonBody,
      ];
      try {
        $send = $this->api->forwardingVerificationEmail($params);
      }
      catch (\Exception $e) {
        // Save audit log.
        $this->saveAuditLog(2, $mail);

        return new ResourceResponse(UtilMessage::getMessage($e));
      }

      if (empty($exist)) {
        // Create relation in notification.
        $create_relation = $notification_service->createRelationNotification($uid, $notification_id, TRUE);
      }

      // Add session var to verified send.
      $_SESSION['notification_verified']['tbo_notifications_verified_send_' . $currentUser->id()] = TRUE;

      // Save audit log.
      $this->saveAuditLog(0, $mail);

      return new ResourceResponse("OK");
    }
    elseif ($params['send_verified'] == 0) {
      // Create relation in notification.
      if (empty($exist)) {
        // Create relation in notification.
        $create_relation = $notification_service->createRelationNotification($uid, $notification_id);
      }
      return new ResourceResponse("OK");
    }

    return new ResourceResponse('Faild');

  }

  /**
   * @param int $type
   * @param string $mail
   */
  public function saveAuditLog($type = 0, $mail = '') {
    // Save audit log.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    // Create array data_log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Cuenta',
      'description' => t('Usuario reenvía correo de verificación de cuenta'),
      'details' => t('Usuario @userName con @mail re envía correo de verificación de cuenta de TigoID',
        [
          '@userName' => $service->getName(),
          '@mail' => $mail,
        ]
      ),
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    if ($type == 1) {
      $data_log['event_type'] = 'Notificaciones';
      $data_log['description'] = t('Usuario consulta alertas y notificaciones');
      $data_log['details'] = t('Usuario @userName consulto alertas y notificaciones',
        [
          '@userName' => $service->getName(),
        ]
      );
    }

    if ($type == 2) {
      $data_log['event_type'] = 'Cuenta';
      $data_log['description'] = t('Usuario no puede reenviar correo de verificación de TigoID');
      $data_log['details'] = t('Usuario @userName no pudo reenviar correo de
verificación de cuenta de TigoID al correo registrado @mail',
        [
          '@userName' => $service->getName(),
          '@mail' => $mail,
        ]
      );
    }

    // Save audit log.
    $service->insertGenericLog($data_log);
  }

}
