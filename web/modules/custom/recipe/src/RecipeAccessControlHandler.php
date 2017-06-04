<?php

namespace Drupal\recipe;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Recipe entity.
 *
 * @see \Drupal\recipe\Entity\Recipe.
 */
class RecipeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\recipe\Entity\RecipeInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished recipe entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published recipe entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit recipe entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete recipe entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add recipe entities');
  }

}
