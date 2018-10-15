<?php

namespace Drupal\tbo_atp\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SearchByProfileEventSubscriber.
 *
 * @package Drupal\tbo_atp\EventSubscriber
 */
class SearchByProfileEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['redirectAtp'];
    return $events;
  }

  /**
   * Code that should be triggered on event specified.
   */
  public function redirectAtp($event) {
    // Check current path.
    $current_path = \Drupal::service('path.current')->getPath();

    // Validate default parameter in search-by-profile.
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_atp');
    $path_associated_lines = $tempStore->get('tbo_atp_search_by_profile_temp_path');
    if ($current_path == $path_associated_lines) {
      $account_id = \Drupal::request()->query->get('p1');
      if (!$account_id) {
        $clientId = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
        $p1 = $tempStore->get('tbo_atp_search_by_profile_temp_p1_' . $clientId);
        if ($p1) {
          $response = new RedirectResponse($current_path . '?p1=' . $p1);
          $response->send();
        }
      }
    }
  }

}
