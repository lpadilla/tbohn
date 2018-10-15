<?php

/**
 * @file
 * Contains notification_detail_entity.page.inc.
 *
 * Page callback for Notification detail entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Notification detail entity templates.
 *
 * Default template: notification_detail_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_notification_detail_entity(array &$variables) {
  // Fetch NotificationDetailEntity Entity Object.
  $notification_detail_entity = $variables['elements']['#notification_detail_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
