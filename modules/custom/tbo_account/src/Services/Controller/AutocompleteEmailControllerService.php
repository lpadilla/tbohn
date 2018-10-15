<?php

namespace Drupal\tbo_account\Services\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TboAccountControllerService.
 *
 * @package Drupal\tbo_account\Services\Controller
 */
class AutocompleteEmailControllerService {

  protected $repository;

  /**
   *
   */
  public function __construct() {
    $this->repository = \Drupal::service('tbo_account.repository');
  }

  /**
   * Autocompleteemail.
   *
   * @return string
   *   Return Hello string.
   */
  public function autocompleteEmail($mail) {
    $suggestedMails = [];
    $mails = $this->repository->autocompleteEmail($mail);

    foreach ($mails as $mail) {
      array_push($suggestedMails, ['name' => $mail->mail]);
    }

    return new JsonResponse($suggestedMails);
  }

}
