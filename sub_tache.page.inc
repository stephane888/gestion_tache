<?php

/**
 * @file
 * Contains sub_tache.page.inc.
 *
 * Page callback for Sub tache entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Sub tache templates.
 *
 * Default template: sub_tache.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_sub_tache(array &$variables) {
  // Fetch SubTache Entity Object.
  $sub_tache = $variables['elements']['#sub_tache'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
