<?php

namespace Drupal\recipe;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\recipe\Entity\RecipeInterface;

/**
 * Defines the storage handler class for Recipe entities.
 *
 * This extends the base storage class, adding required special handling for
 * Recipe entities.
 *
 * @ingroup recipe
 */
class RecipeStorage extends SqlContentEntityStorage implements RecipeStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(RecipeInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {recipe_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {recipe_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(RecipeInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {recipe_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('recipe_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
