<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Sub tache entity.
 *
 * @see \Drupal\gestion_tache\Entity\SubTache.
 */
class SubTacheAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\gestion_tache\Entity\SubTacheInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished sub tache entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published sub tache entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit sub tache entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete sub tache entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add sub tache entities');
  }


}
