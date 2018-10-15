<?php

namespace Drupal\tbo_services\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\tbo_services\Services\Controller\ManagePqrsLogicController;

/**
 * Class ManagePqrsController.
 *
 * @package Drupal\tbo_services\Controller
 */
class ManagePqrsController extends ControllerBase {

  protected $serviceLogicController;

  /**
   * {@inheritdoc}
   */
  public function __construct(ManagePqrsLogicController $managePqrsLogicController) {
    $this->serviceLogicController = $managePqrsLogicController;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tbo_services.manage_pqrs_logic_controller')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function redirectPqrs($option, $url) {
    return $this->serviceLogicController->redirectPqrs($option, $url);
  }

}
