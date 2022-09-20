<?php

/**
 * @file
 * Contains app_prime.page.inc.
 *
 * Page callback for App prime entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for App prime templates.
 *
 * Default template: app_prime.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_app_prime(array &$variables) {
  // Fetch AppPrime Entity Object.
  $app_prime = $variables['elements']['#app_prime'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
