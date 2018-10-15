<?php

namespace Drupal\tbo_user\Services\Controller;

use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class EmulateSessionControllerService.
 *
 * @package Drupal\tbo_user\Service\Controller
 */
class EmulateSessionControllerService {

  protected $service_log;

  /**
   *
   */
  public function __construct() {
    $this->service_log = \Drupal::service('tbo_core.audit_log_service');
  }

  /**
   *
   */
  public function emulateSession($user) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $service = \Drupal::service('masquerade');

    if ($user != 'unmasquerade') {
      $uid = \Drupal::currentUser()->id();
      $account = User::load($uid);

      $account_sup = User::load($user);
      $name_sup = $account_sup->get('full_name')->value;
      if ($name_sup == NULL || $name_sup == '') {
        $name_sup = $account_sup->getDisplayName();
      }

      // Get name rol.
      $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);

      // Save Audit log.
      $this->service_log->loadName();
      $name = $this->service_log->getName();
      $description = 'El usuario ' . $rol . ' se está haciendo pasar por Administrador de Empresa';
      $details = 'Usuario ' . $name . ' se esta haciendo pasar por ' . $name_sup;
      $this->saveAuditLog($description, $details);

      $target_account = $service->entityTypeManager
        ->getStorage('user')
        ->load($user);
      $service->switchTo($target_account);
      drupal_set_message('Ahora usted está haciendose pasar por ' . $name_sup);
      $_SESSION['old_user'] = $uid;
      $_SESSION['emular_role'] = $rol;
      $_SESSION['emular_referer'] = \Drupal::request()->server->get('HTTP_REFERER');

      $block_manager = \Drupal::service('plugin.manager.block');
      $config = [];
      $plugin_block = $block_manager->createInstance('unmasquerade_block', $config);
      // Some blocks might implement access check.
      $access_result = $plugin_block->access(\Drupal::currentUser());

      // Return empty render array if user doesn't have access.
      if (!$access_result) {
        return [
          '#type' => 'markup',
          '#markup' => t('No posee los permisos necesarios para acceder a este bloque'),
        ];
      }
      $_SESSION['ummasquerade_block'] = $plugin_block;

      return new RedirectResponse(Url::fromUri('internal:/tbo_general/selector/0')
        ->toString());
    }
    else {
      $account_sup = User::load(\Drupal::currentUser()->id());
      $name_sup = $account_sup->get('full_name')->value;
      if ($name_sup == NULL || $name_sup == '') {
        $name_sup = $account_sup->getDisplayName();
      }
      $service->switchBack();
      drupal_set_message(t('Suplantación de sesión terminada'));
      unset($_SESSION['ummasquerade_block']);
      unset($_SESSION['emular_role']);

      // Save Audit log.
      $this->service_log->loadName();
      $name = $this->service_log->getName();
      $description = 'Usuario deja de suplantar usuario Admin Empresa';
      $details = 'Usuario ' . $name . ' ya no se esta haciendo pasar por ' . $name_sup;
      $this->saveAuditLog($description, $details);

      $referer = $_SESSION['emular_referer'];
      if (isset($_SESSION['emular_referer'])) {
        unset($_SESSION['emular_referer']);
      }

      if (isset($_SESSION['company'])) {
        unset($_SESSION['company']);
      }

      if ($referer == null) {
        $referer_translate = t('internal:/emular-sesion');
        $referer = $referer_translate->getUntranslatedString();
      }

      return new RedirectResponse(Url::fromUri($referer)
        ->toString());
    }
  }

  /**
   * @return bool
   */
  public function saveAuditLog($description = '', $details = '') {
    // Create array data[].
    $data = [
      'companySegment' => 'segmento',
      'event_type' => 'Cuenta',
      'description' => $description,
      'details' => $details,
    ];

    // Save audit log.
    $this->service_log->insertGenericLog($data);
  }

}
