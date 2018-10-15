<?php

/**
 * @file
 * Contains menu_tab_entity.page.inc.
 *
 * Page callback for Menu tab entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Menu tab entity templates.
 *
 * Default template: menu_tab_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_menu_tab_entity(array &$variables) {
  // Fetch MenuTabEntity Entity Object.
  $menu_tab_entity = $variables['elements']['#menu_tab_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
