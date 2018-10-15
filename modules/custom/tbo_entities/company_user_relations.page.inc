<?php

/**
 * @file
 * Contains company_user_relations.page.inc.
 *
 * Page callback for Company user relations entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Company user relations templates.
 *
 * Default template: company_user_relations.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_company_user_relations(array &$variables) {
  // Fetch CompanyUserRelations Entity Object.
  $company_user_relations = $variables['elements']['#company_user_relations'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
