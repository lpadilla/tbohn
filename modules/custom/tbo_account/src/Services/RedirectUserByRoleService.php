<?php

namespace Drupal\tbo_account\Services;

use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class RedirectUserByRoleService.
 *
 * @package Drupal\tbo_account\Services
 */
class RedirectUserByRoleService {

  /**
   *
   */
  public function __construct() {
  }

  /**
   *
   */
  public function redirectByRole() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $roles = $account->getRoles();

    if (in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return new RedirectResponse(Url::fromUri('internal:/empresa')
        ->toString());
    }

    if (in_array('admin_company', $roles)) {
      return new RedirectResponse(Url::fromUri('internal:/auto_create/account')
        ->toString());
    }
  }

}
