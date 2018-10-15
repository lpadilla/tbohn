<?php

namespace Drupal\tigoid\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class LoginHandler.
 *
 * @package Drupal\tigoid
 */
class LoginHandler extends ControllerBase {

  /**
   * Handler class.
   *
   * @var \Drupal\tigoid\Services\Controller\LoginHandlerControllerService
   */
  protected $configClass;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->configClass = \Drupal::service('tigoid.login_handler');
  }

  /**
   * Evaluate function.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param string $action
   *   Puede tener dos estados que se describen a continuación.
   *    - login: Cuando cliquean en el boton iniciar sesión.
   *    - create: Cuando cliquean en crear cuenta.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   */
  public function evaluate(Request $request, $action = 'login') {
    // TODO Implementar funcionalidad para saber si se sigue el nuevo flujo.
    $blockConfig = $this->config('block.block.logintigoid')
      ->get('settings');
    $newFlow = (isset($blockConfig['others']['config']['new_flow'])) ? $blockConfig['others']['config']['new_flow'] : FALSE;

    // Se agregan en la sesión para ser usandos posteriormente de ser necesario.
    $_SESSION['click_login_info'] = [
      'new_flow' => $newFlow,
      'button' => $action,
    ];
    $url = $this->configClass->evaluate();
    return new RedirectResponse($url);
  }

}
