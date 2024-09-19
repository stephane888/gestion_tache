<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the App project entity.
 *
 * @see \Drupal\gestion_tache\Entity\AppProject.
 */
class AppProjectAccessControlHandler extends EntityAccessControlHandler {
  /**
   *
   * @var \Drupal\gestion_tache\Services\Api\AccessEntitiesController
   */
  protected static $AccessEntitiesController;
  
  /**
   *
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    
    /** @var \Drupal\gestion_tache\Entity\AppProjectInterface $entity */
    switch ($operation) {
      
      case 'view':
        if (self::getAccessEntitiesController()->filterToLoadEntities($entity))
          return AccessResult::allowed();
        else
          return AccessResult::forbidden();
      // if (!$entity->isPublished()) {
      // $permission = $this->checkOwn($entity, 'view unpublished', $account);
      // if (!empty($permission)) {
      // return AccessResult::allowed();
      // }
      // return AccessResult::allowedIfHasPermission($account, 'view unpublished
      // app project entities');
      // }
      // $permission = $this->checkOwn($entity, $operation, $account);
      // if (!empty($permission)) {
      // return AccessResult::allowed();
      // }
      // return AccessResult::allowedIfHasPermission($account, 'view published
      // app project entities');
      
      case 'update':
        if (self::getAccessEntitiesController()->accessToUpdateEntity($entity))
          return AccessResult::allowed();
        else {
          return AccessResult::allowed();
          // return AccessResult::forbidden("Vous n'avez pas les euto ok.");
        }
      
      // $permission = $this->checkOwn($entity, $operation, $account);
      // if (!empty($permission)) {
      // return AccessResult::allowed();
      // }
      // return AccessResult::allowedIfHasPermission($account, 'edit app project
      // entities');
      
      case 'delete':
        if (self::getAccessEntitiesController()->accessToDeleteEntity($entity))
          return AccessResult::allowed();
        else
          return AccessResult::forbidden();
      // $permission = $this->checkOwn($entity, $operation, $account);
      // if (!empty($permission)) {
      // return AccessResult::allowed();
      // }
      // return AccessResult::allowedIfHasPermission($account, 'delete app
      // project entities');
    }
    
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add app project entities');
  }
  
  /**
   * Test for given 'own' permission.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param
   *        $operation
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return string|null The permission string indicating it's allowed.
   */
  protected function checkOwn(EntityInterface $entity, $operation, AccountInterface $account) {
    $status = $entity->isPublished();
    $uid = $entity->getOwnerId();
    
    $is_own = $account->isAuthenticated() && $account->id() == $uid;
    if (!$is_own) {
      return;
    }
    
    $bundle = $entity->bundle();
    
    $ops = [
      'create' => '%bundle add own %bundle entities',
      'view unpublished' => '%bundle view own unpublished %bundle entities',
      'view' => '%bundle view own entities',
      'update' => '%bundle edit own entities',
      'delete' => '%bundle delete own entities'
    ];
    $permission = strtr($ops[$operation], [
      '%bundle' => $bundle
    ]);
    
    if ($operation === 'view unpublished') {
      if (!$status && $account->hasPermission($permission)) {
        return $permission;
      }
      else {
        return NULL;
      }
    }
    if ($account->hasPermission($permission)) {
      return $permission;
    }
    
    return NULL;
  }
  
  /**
   *
   * @return \Drupal\gestion_tache\Services\Api\AccessEntitiesController
   */
  private function getAccessEntitiesController() {
    if (!self::$AccessEntitiesController) {
      self::$AccessEntitiesController = \Drupal::service('gestion_tache_v2.access_entity_controller');
    }
    return self::$AccessEntitiesController;
  }
  
}
