<?php

namespace Drupal\tbo_general\TwigExtension;

/**
 * Class TboThemingTwigExtension.
 *
 * @package Drupal\tbo_general\TwigExtension
 */
class TboThemingTwigExtension extends \Twig_Extension {

  /**
   * Get functions.
   *
   * @return array|\Twig_SimpleFunction[]
   *   Twig_SimpleFunction object.
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('verifyCardAccess', [$this, 'verifyCardAccess']),
    ];
  }

  /**
   * Get name.
   *
   * @return string
   *   Extension name.
   */
  public function getName() {
    return 'tbo_general.twig.extension';
  }

  /**
   * Verify the card access permission for the actual company.
   *
   * @param string $plugin_id
   *   Plugin Block id.
   *
   * @return bool
   *   Validated card access.
   */
  public function verifyCardAccess($plugin_id = NULL) {
    if ($plugin_id != NULL && $plugin_id != '') {
      // First we verify if the module exist and is enabled.
      $moduleHandler = \Drupal::service('module_handler');
      if ($moduleHandler->moduleExists('tbo_permissions')) {
        // Now we check if the cards access validation is enabled,
        // in the TBO Permissions configuration.
        $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');
        $validateAllCardsAccessByCompany = $permissionsRepository->getCheckAccessCardConfig();

        if ($validateAllCardsAccessByCompany == 'true') {
          if (isset($_SESSION['company']['id']) && isset($plugin_id)) {
            // Validate this company access permission to this card.
            return $permissionsRepository->getCardAccess($_SESSION['company']['id'], $plugin_id);
          }
        }
      }
    }

    return TRUE;
  }

}
