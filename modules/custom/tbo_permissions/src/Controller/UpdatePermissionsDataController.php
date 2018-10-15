<?php

namespace Drupal\tbo_permissions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\tbo_permissions\Services\Controller\UpdatePermissionsDataLogicController;

/**
 * Class UpdatePermissionsDataController.
 */
class UpdatePermissionsDataController extends ControllerBase {

  protected $serviceLogicController;

  /**
   * {@inheritdoc}
   */
  public function __construct(UpdatePermissionsDataLogicController $updatePermissionsDataLogicController) {
    $this->serviceLogicController = $updatePermissionsDataLogicController;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tbo_permissions.update_permissions_data_logic_controller')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updatePermissions() {
    return $this->serviceLogicController->updatePermissions();
  }

}
