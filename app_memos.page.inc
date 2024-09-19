<?php

/**
 * @file
 * Contains app_memos.page.inc.
 *
 * Page callback for App memos entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for App memos templates.
 *
 * Default template: app_memos.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_app_memos(array &$variables) {
  // Fetch AppMemos Entity Object.
  $app_memos = $variables['elements']['#app_memos'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
