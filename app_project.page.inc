<?php

/**
 *
 * @file
 * Contains app_project.page.inc.
 *
 * Page callback for App project entities.
 */
use Drupal\Core\Render\Element;

/**
 * Prepares variables for App project templates.
 *
 * Default template: app_project.html.twig.
 *
 * @param array $variables
 *        An associative array containing:
 *        - elements: An associative array containing the user information and
 *        any
 *        - attributes: HTML attributes for the containing element.
 */
function template_preprocess_app_project(array &$variables) {
  $app_project = \Drupal::routeMatch()->getParameter('app_project');
  if (!empty($app_project)) {
    dump($app_project->toArray());
  }
  // Fetch AppProject Entity Object.
  $app_project = $variables['elements']['#app_project'];
  
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
