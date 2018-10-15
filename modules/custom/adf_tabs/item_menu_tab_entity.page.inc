<?php

/**
 * @file
 * Contains item_menu_tab_entity.page.inc.
 *
 * Page callback for Item menu tab entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Item menu tab entity templates.
 *
 * Default template: item_menu_tab_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_item_menu_tab_entity(array &$variables) {
  // Fetch ItemMenuTabEntity Entity Object.
  $item_menu_tab_entity = $variables['elements']['#item_menu_tab_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
