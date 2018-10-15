<?php

namespace Drupal\adf_tabs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MenuTabController extends ControllerBase {

  /**
   * @var configurationInstance
   *   Drupal\adf_tabs\Plugin\Config\Controller\MenuTabControllerClass
   */
  protected $configurationInstance;

  /**
   * MenuTabController constructor.
   */
  public function __construct() {
    $this->configurationInstance = \Drupal::service('adf_tabs.menu_tab_controller_class');
  }

  /**
   * Render block in mobile view.
   *
   * @param $id
   *
   * @return mixed
   */
  public function mobileViewBlock(Request $request) {
    $id = $_GET['idblock'];
    return $this->configurationInstance->mobileViewBlock($id);
  }

}
