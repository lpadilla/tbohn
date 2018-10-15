<?php

namespace Drupal\tigoid\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\tigoid_migrate\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change path '/user/login' to '/user/old_login'.
    $tigoid_migrate_config = \Drupal::config('tigoid.migrate');
    if ($route = $collection->get('user.login')) {
      $route->setPath('/user/old_login');
    }

    if ($tigoid_migrate_config->get('active_migration') && (\Drupal::moduleHandler()->moduleExists('tigoid_migrate'))) {
      if ($route = $collection->get('tigoid_migrate.login_form')) {
        $route->setPath('/user/login');
      }
    }
    else {
      if ($route = $collection->get('tigoid.authorize')) {
        $route->setPath('/user/login');
      }
    }
  }

}
