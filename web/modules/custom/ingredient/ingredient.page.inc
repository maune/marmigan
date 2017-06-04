<?php

/**
 * @file
 * Contains ingredient.page.inc.
 *
 * Page callback for Ingredient entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Ingredient templates.
 *
 * Default template: ingredient.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ingredient(array &$variables) {
  // Fetch Ingredient Entity Object.
  $ingredient = $variables['elements']['#ingredient'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
