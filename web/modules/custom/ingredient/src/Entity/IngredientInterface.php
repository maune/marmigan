<?php

namespace Drupal\ingredient\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Ingredient entities.
 *
 * @ingroup ingredient
 */
interface IngredientInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Ingredient name.
   *
   * @return string
   *   Name of the Ingredient.
   */
  public function getName();

  /**
   * Sets the Ingredient name.
   *
   * @param string $name
   *   The Ingredient name.
   *
   * @return \Drupal\ingredient\Entity\IngredientInterface
   *   The called Ingredient entity.
   */
  public function setName($name);

  /**
   * Gets the Ingredient creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Ingredient.
   */
  public function getCreatedTime();

  /**
   * Sets the Ingredient creation timestamp.
   *
   * @param int $timestamp
   *   The Ingredient creation timestamp.
   *
   * @return \Drupal\ingredient\Entity\IngredientInterface
   *   The called Ingredient entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Ingredient published status indicator.
   *
   * Unpublished Ingredient are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Ingredient is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Ingredient.
   *
   * @param bool $published
   *   TRUE to set this Ingredient to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ingredient\Entity\IngredientInterface
   *   The called Ingredient entity.
   */
  public function setPublished($published);

  /**
   * Gets the Ingredient revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Ingredient revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\ingredient\Entity\IngredientInterface
   *   The called Ingredient entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Ingredient revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Ingredient revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\ingredient\Entity\IngredientInterface
   *   The called Ingredient entity.
   */
  public function setRevisionUserId($uid);

}
