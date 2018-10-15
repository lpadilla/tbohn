<?php

namespace Drupal\tbo_account_bo\Services\Controller;

use Drupal\Core\Url;
use Masterminds\HTML5\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\rest\ResourceResponse;


/**
 * Class TboAccountBoControllerService
 * @package Drupal\tbo_account_bo\Services
 */
class TboAccountBoControllerService {

  protected $user;
  protected $name;
  protected $repository;

  public function __construct()
  {
    $this->repository = \Drupal::service('tbo_account_bo.repository');
  }

  public function DesactivarUsuario($email,$nombre)
  {
      $respuesta = $this->repository->DesactivarUsuario($email,$nombre);
      return $respuesta;
  }
}
