<?php

namespace Drupal\tbo_account_bo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class tboAccountBoController extends ControllerBase {

  protected $service_controller;

  /**
   * tboAccountController constructor.
   */
  public function __construct() {
    $this->user = \Drupal::currentUser();
    $this->service_controller = \Drupal::service('tbo_account_bo.tbo_account_controller_service');
  }

  /**
   * Implements function DesactivarUsuario
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function desactivar_usuario(Request $request)
  {
      if ( (isset($_POST)) && ($_POST["email"] != "") )
      {
          $respuesta = $this->service_controller->DesactivarUsuario($_POST["email"], $_POST["nombre"]);
          if ($respuesta['info'] == 'exito')
          {
              drupal_set_message(t($respuesta['message']));
          }
          elseif ($respuesta['info'] == 'error')
          {
              drupal_set_message(t($respuesta['message']), 'error');
          }
      }
      else
      {
          drupal_set_message(t('Es necesario indicar el correo del usuario a bloquear, por favor intenta de nuevo'), 'error');
      }

      $redirect_path = "/usuarios";
      $url = url::fromUserInput($redirect_path);
      $this->setRedirectUrl($url);
  }
}
