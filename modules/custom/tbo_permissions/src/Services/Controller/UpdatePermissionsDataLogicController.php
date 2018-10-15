<?php

namespace Drupal\tbo_permissions\Services\Controller;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class UpdatePermissionsDataLogicController.
 *
 * @package Drupal\tbo_permissions\Controller
 */
class UpdatePermissionsDataLogicController extends ControllerBase {

  /**
   * Generate and send the daily report in Excel format.
   *
   * @return array
   *   Return data response.
   */
  public function updatePermissions() {
    $database = \Drupal::database();
    $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');
    $currentCards = $permissionsRepository->getAllCards();
    $finalMarkup = '';

    try {
      // We walk the array, to check if there is a card
      // with permissions set on the companies.
      foreach ($currentCards as $pluginId => $cardInfo) {
        $queryCardsAccess = $database->select('cards_access_by_company_permissions', 'ca')
          ->fields('ca', ['id'])
          ->condition('ca.block_id', $pluginId)
          ->range(0, 1);
        $resultCardsAccess = $queryCardsAccess->execute()->fetchAll();

        // There is no records associated with this plugin_id.
        if (count($resultCardsAccess) == 0) {

          $markupInfo = $permissionsRepository->createCardPermissionsSet($pluginId);

          // Add to the final markup.
          foreach ($markupInfo as $rowLog) {
            $finalMarkup .= $rowLog . '<br>';
          }
        }
      }

      if ($finalMarkup == '') {
        $finalMarkup = t('No se han creado nuevos registros de permisos de acceso a cards.');
      }

      return [
        '#type' => 'markup',
        '#markup' => $finalMarkup,
      ];
    }
    catch (\Exception $e) {
      return [
        '#type' => 'markup',
        '#markup' => UtilMessage::getMessage($e),
      ];
    }
  }

}
