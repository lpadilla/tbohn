<?php

namespace Drupal\tbo_account\Services;

/**
 * Class AccountService.
 *
 * @package Drupal\tbo_account\Services
 */
class UpdateUserAccountService {

  /**
   *
   */
  public function __construct() {
  }

  /**
   *
   */
  public function getUserUpdateForm() {
    $twig = \Drupal::service('twig');
    $twig->addGlobal('show_create_user', TRUE);
    $form = \Drupal::formBuilder()
      ->getForm('Drupal\tbo_account\Form\EditUserDataForm');
    return $form;
  }

}
