<?php

namespace Drupal\ingredient;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class IngredientStorage extends SqlContentEntityStorage implements IngredientStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(IngredientInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {ingredient_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {ingredient_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(IngredientInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {ingredient_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('ingredient_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
