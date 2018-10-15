<?php

/**
 * @file
 * Contains log_import_data_entity.page.inc.
 *
 * Page callback for Log import data entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Log import data entity templates.
 *
 * Default template: log_import_data_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_log_import_data_entity(array &$variables) {
  // Fetch LogImportDataEntity Entity Object.
  $log_import_data_entity = $variables['elements']['#log_import_data_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
