<?php

namespace Drupal\ingredient;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Ingredient entity.
 *
 * @see \Drupal\ingredient\Entity\Ingredient.
 */
class IngredientAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ingredient\Entity\IngredientInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished ingredient entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published ingredient entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit ingredient entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete ingredient entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add ingredient entities');
  }

}
