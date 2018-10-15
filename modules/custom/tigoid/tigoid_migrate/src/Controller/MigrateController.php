<?php

namespace Drupal\tigoid_migrate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Class MigrateController.
 *
 * @package Drupal\tigoid_migrate\Controller
 */
class MigrateController extends ControllerBase {

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function startWizard() {

    // $account = \Drupal::currentUser();
    $tol_user = \Drupal::service('tol.user');
    $form_builder = \Drupal::formBuilder();

    $lines_entities = $tol_user->lines->getAll();

    $lines = [];

    foreach ($lines_entities as $entity) {
      $lines[] = $entity->getMsisdn();
    }

    $tigoid_form = $form_builder->getForm('Drupal\tigoid_migrate\Form\ConnectCurrentUserForm');
    $tigoid_form['openid_connect_client_tigoid_connect']['#value'] = $this->t('Siguiente');

    $build = [
      '#theme' => 'wizard_start',
      '#lines' => $lines,
      '#tigoid_form' => $tigoid_form,
      '#cache' => [
        'max-age' => 60,
        'contexts' => [
          'user',
        ],
      ],
      '#attached' => [
        'library' => [
          'tigoid_migrate/update_account',
        ],
      ],
    ];

    return $build;

  }

  /**
   *
   */
  public function access(AccountInterface $account) {
    $connected = tigoid_migrate_drupal_account_is_connected($account->id());
    return AccessResult::allowedIf($connected == FALSE);
  }

}
