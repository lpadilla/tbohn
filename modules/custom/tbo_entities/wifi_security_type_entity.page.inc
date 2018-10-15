<?php

/**
 * @file
 * Contains wifi_security_type_entity.page.inc.
 *
 * Page callback for Wifi security type entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Wifi security type entity templates.
 *
 * Default template: wifi_security_type_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_wifi_security_type_entity(array &$variables) {
  // Fetch WifiSecurityTypeEntity Entity Object.
  $wifi_security_type_entity = $variables['elements']['#wifi_security_type_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
