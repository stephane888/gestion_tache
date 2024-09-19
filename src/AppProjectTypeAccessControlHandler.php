<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

class AppProjectTypeAccessControlHandler extends EntityAccessControlHandler {
  
  /**
   *
   * @bug : ne fonctionne pas lorsqu'on charge les données à partir loadMultiple sur drupal 9.5.8.
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // on utilise la logique parente.
    return parent::checkAccess($entity, $operation, $account);
  }
  
}