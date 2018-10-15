<?php

namespace Drupal\tbo_emulate_bo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Class EmulateSessionBo.
 *
 * @package Drupal\tbo_emulate_bo\Controller
 */
class EmulateSessionBo extends ControllerBase {
  
  public function emulateSessionBo($user) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $service = \Drupal::service('masquerade');

    if ($user != 'unmasquerade') {
      $log = AuditLogEntity::create();
      $segment = 'segmento';
      $uid = \Drupal::currentUser()->id();
      $account = User::load($uid);

      //Load fields account
      $account_fields = \Drupal::currentUser()->getAccount();
      if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
        $name = $account_fields->full_name;
      } else {
        $name = \Drupal::currentUser()->getAccountName();
      }

      //get name rol
      $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);

      $account_sup = User::load($user);
      $name_sup = $account_sup->getDisplayName();
      $log->set('created', time());
      $log->set('company_segment', $segment);
      $log->set('user_id', $uid);
      $log->set('user_role', $rol);
      $log->set('event_type', 'Cuenta');
      $log->set('description', 'El usuario '. $rol .' se está haciendo pasar por Administrador de Empresa');
      $log->set('details', 'Usuario ' . $name . ' se esta haciendo pasar por ' . $name_sup);
      $log->save();

      $target_account = $service->entityTypeManager
        ->getStorage('user')
        ->load($user);
      $service->switchTo($target_account);
      drupal_set_message('Ahora usted está haciendose pasar por ' . $name_sup);
     $_SESSION['old_user'] = $uid ;
     $_SESSION['emular_role'] = $account->get('roles')
       ->getValue()[0]['target_id'];

      $block_manager = \Drupal::service('plugin.manager.block');
      $config = [];
      $plugin_block = $block_manager->createInstance('unmasquerade_block', $config);
      // Some blocks might implement access check.
      $access_result = $plugin_block->access(\Drupal::currentUser());

      // Return empty render array if user doesn't have access.
      if (!$access_result) {
        return [
          '#type' => 'markup',
          '#markup' => $this->t('No posee los permisos necesarios para acceder a este bloque'),
        ];
      }
     $_SESSION['ummasquerade_block'] = $plugin_block;

      return new RedirectResponse(Url::fromUri('internal:/tbo_emulate_bo/selector/0')
        ->toString());
    }
    else {
      $account_sup = User::load(\Drupal::currentUser()->id());
      $name_sup = $account_sup->getDisplayName();
        $service->switchBack();
        drupal_set_message('Suplantación de sesión terminada');
        $uid = \Drupal::currentUser()->id();
        $account = User::load($uid);
        unset($_SESSION['ummasquerade_block']);
        unset($_SESSION['emular_role']);
        
        unset($_SESSION['company']); 

        $log = AuditLogEntity::create();
        $segment = 'segmento'; 
        $uid = \Drupal::currentUser()->id();
        $account = User::load($uid);
      
        //Load fields account
        $account_fields = \Drupal::currentUser()->getAccount();
        $name = $account_fields->get('full_name')->getValue()[0]['value'];
        
        if (!isset($name) && empty($name)) {
          $name = \Drupal::currentUser()->getAccountName();
        }

        //get name rol
        $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);
        
        $log->set('created', time());
        $log->set('company_segment', $segment);
        $log->set('user_id', $uid);
        $log->set('user_role', $rol);
        $log->set('event_type', 'Cuenta');
        $log->set('description', 'Usuario deja de suplantar usuario Admin Empresa');
        $log->set('details', 'Usuario ' . $name . ' ya no se esta haciendo pasar por ' . $name_sup);
        $log->save();

        return new RedirectResponse(Url::fromUri('internal:/' . '<front>')
          ->toString());
    }
  }
}