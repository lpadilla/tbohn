<?php

namespace Drupal\tbo_account\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AutocreateAccountController.
 */
class AutocreateAccountController extends ControllerBase {

  /**
   * Autocreateaccount.
   *
   * @return string
   *   Return string.
   */
  public function autocreateAccount($type) {

    $response = 'Error cargando página';

    if ($type == 'user') {
      $request = \Drupal::service('tbo_account.update_user');
      $response = $request->getUserUpdateForm();
    }

    if ($type == 'account') {
      $request = \Drupal::service('tbo_account.create_account');
      $response = $request->getCreateAccountForm();
    }

    if ($type == 'role') {
      $request = \Drupal::service('tbo_account.redirect_by_role');
      $response = $request->redirectByRole();
    }

    if ($response) {
      return $response;
    }
    else {
      return [];
    }
  }

}
