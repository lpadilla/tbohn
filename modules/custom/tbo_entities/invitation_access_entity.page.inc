<?php

/**
 * @file
 * Contains invitation_access_entity.page.inc.
 *
 * Page callback for Invitation access entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Invitation access entity templates.
 *
 * Default template: invitation_access_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_invitation_access_entity(array &$variables) {
  // Fetch InvitationAccessEntity Entity Object.
  $invitation_access_entity = $variables['elements']['#invitation_access_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
