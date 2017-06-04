<?php

namespace Drupal\ingredient;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\ingredient\Entity\IngredientInterface;

/**
 * Defines the storage handler class for Ingredient entities.
 *
 * This extends the base storage class, adding required special handling for
 * Ingredient entities.
 *
 * @ingroup ingredient
 */
interface IngredientStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Ingredient revision IDs for a specific Ingredient.
   *
   * @param \Drupal\ingredient\Entity\IngredientInterface $entity
   *   The Ingredient entity.
   *
   * @return int[]
   *   Ingredient revision IDs (in ascending order).
   */
  public function revisionIds(IngredientInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Ingredient author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Ingredient revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\ingredient\Entity\IngredientInterface $entity
   *   The Ingredient entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(IngredientInterface $entity);

  /**
   * Unsets the language for all Ingredient with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
