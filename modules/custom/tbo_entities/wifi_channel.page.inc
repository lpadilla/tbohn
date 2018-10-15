<?php

/**
 * @file
 * Contains wifi_channel.page.inc.
 *
 * Page callback for Wifi channel entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Wifi channel templates.
 *
 * Default template: wifi_channel.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_wifi_channel(array &$variables) {
  // Fetch WifiChannel Entity Object.
  $wifi_channel = $variables['elements']['#wifi_channel'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
