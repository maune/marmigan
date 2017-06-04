<?php

/**
 * @file
 * Contains recipe.page.inc.
 *
 * Page callback for Recipe entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Recipe templates.
 *
 * Default template: recipe.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_recipe(array &$variables) {
  // Fetch Recipe Entity Object.
  $recipe = $variables['elements']['#recipe'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
