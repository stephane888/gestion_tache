<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

class GestionTache {
  
  /**
   * Permet d'ajouter l'uid dans le flux encours.
   * Le processus doit integrer l'authentification.
   * Cette function est utile pour les sauvegardes automatiques, (i.e quand le
   * sql est construit à distance ).
   */
  static function addCurrentUidOnfield() {
    //
  }
  
  static function getAvailableUserForProject(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, $cacheable = true) {
    $entity_type_id = $entity->getEntityType()->getBundleEntityType();
    /**
     *
     * @var \Drupal\gestion_tache\Entity\AppProjectType $entityType
     */
    $entityType = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity->bundle());
    return $entityType->getListOptionsUsers();
  }
  
}