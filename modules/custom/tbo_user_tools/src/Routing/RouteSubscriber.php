<?php

namespace Drupal\tbo_user_tools\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change routing entity form user.
    if ($route = $collection->get('user.multiple_cancel_confirm')) {
      $route->setDefault('_form', '\Drupal\tbo_user_tools\Form\UserMultipleCancelConfirmForm');
    }
  }

}
