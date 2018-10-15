<?php

/**
 * @file
 * Contains audit_log_entity.page.inc.
 *
 * Page callback for Audit log entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Audit log entity templates.
 *
 * Default template: audit_log_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_audit_log_entity(array &$variables) {
  // Fetch AuditLogEntity Entity Object.
  $audit_log_entity = $variables['elements']['#audit_log_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
