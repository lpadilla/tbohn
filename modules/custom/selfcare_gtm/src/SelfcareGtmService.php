<?php

namespace Drupal\selfcare_gtm;

/**
 * Class SelfcareGtmService.
 *
 * @package Drupal\selfcare_gtm
 */
class SelfcareGtmService implements SelfcareGtmServiceInterface {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   *
   */
  public function push($event, $category = 'undefined', $action = 'undefined', $label = 'undefined', $value = 'undefined', $aditional_data = []) {
    // Append tigoId Event.
    $eventData = [
      'event' => $event,
      'category' => $category,
      'action' => $action,
      'label' => $label,
      'value' => $value,
    ];

    $aditional_data = [
      'scope' => [
        'email' => 'yovannydrb@hotmail.com',
        'profile' => 'admin',
      ],
    ];

    if (!empty($aditional_data)) {
      $params = array_merge($eventData, $aditional_data);
    }
    else {
      $params = $eventData;
    }

    $_SESSION['gtm_event'][] = $params;
  }

  /**
   *
   */
  public function pull() {

  }

}
