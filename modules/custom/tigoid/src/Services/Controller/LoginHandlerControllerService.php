<?php

namespace Drupal\tigoid\Services\Controller;

use Drupal\Core\Url;

/**
 * Manage config a 'LoginHandlerControllerService'.
 */
class LoginHandlerControllerService {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // TODO validar cookie de tigoid y si ya se ha conectado
    // redireccionar a tigoid.
    if (isset($_COOKIE['AUTHORIZATION_OP']) && ($_COOKIE['AUTHORIZATION_OP'] == 'tigoid')) {
      return Url::fromRoute('tigoid.authorize')->toString();
    }
    else {
      /*if (\Drupal::moduleHandler()->moduleExists('tigoid_migrate')) {
      $tigoid_migrate_config = \Drupal::config('tigoid.migrate');
      if($tigoid_migrate_config->get('active_migration')) {
      return new RedirectResponse(\Drupal::url('tigoid_migrate.login_form'));
      }
      }*/
      return Url::fromRoute('tigoid.authorize')->toString();
    }
  }

}
