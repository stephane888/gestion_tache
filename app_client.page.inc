<?php

/**
 * @file
 * Contains app_client.page.inc.
 *
 * Page callback for App client entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for App client templates.
 *
 * Default template: app_client.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_app_client(array &$variables) {
  // Fetch AppClient Entity Object.
  $app_client = $variables['elements']['#app_client'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
