<?php

namespace Drupal\gestion_tache\Services\Api;

use Drupal\gestion_tache\GestionTache;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\gestion_tache\ExceptionGestionTache;
use Drupal\gestion_tache\Entity\AppEntityInterface;

/**
 * --
 *
 * @author stephane
 *        
 */
class AccessEntitiesController extends BaseApi {
  
  /**
   * Permet de determiner si l'entity de configuration autorise un access.
   *
   * @var boolean
   */
  protected $bundleAcsess = null;
  
  /**
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $EntityTypeManager;
  
  /**
   * --
   */
  function __construct(EntityTypeManager $EntityTypeManager) {
    $this->EntityTypeManager = $EntityTypeManager;
  }
  
  /**
   * Permet de determiner si un utilisateur a le droit d'editer une entitée de
   * config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entityType
   */
  public function accessToSaveEntity(AppEntityInterface $entity) {
    $status = false;
    if (GestionTache::userIsAdministrator())
      $status = true;
    else {
      if (!$this->checkAccessOnBundble($entity))
        throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour acceder à cette ressource ", 403);
      $status = true;
    }
    return $status;
  }
  
  /**
   * Permet de determiner si un utilisateur a le droit d'editer une entitée de
   * config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entityType
   */
  public function accessToUpdateEntity(AppEntityInterface $entity) {
    $status = false;
    if (GestionTache::userIsAdministrator())
      $status = true;
    else {
      // il faudra faire envoyé ces erreurs par mail, car cela ressemble à une
      // tentative de hacking.
      if (!$this->checkAccessOnBundble($entity) || ($entity->IsPrivate() && $entity->getOwnerId() != GestionTache::UserId()))
        throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour modifier à cette ressource ", 403);
      // on ajoutera d'autre regles ici.
      $status = true;
    }
    return $status;
  }
  
  /**
   * Permet de determiner si un utilisateur a le droit d'editer une entitée de
   * config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entityType
   */
  public function accessToDeleteEntity(AppEntityInterface $entity) {
    $status = false;
    if (GestionTache::userIsAdministrator())
      $status = true;
    else {
      // il faudra faire envoyé ces erreurs par mail, car cela ressemble à une
      // tentative de hacking.
      if (!$this->checkAccessOnBundble($entity))
        throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour modifier à cette ressource ", 403);
      // on ajoutera d'autre regles ici.
      if ($entity->getOwnerId() == GestionTache::UserId())
        $status = true;
    }
    return $status;
  }
  
  /**
   * Verifie que c'est uniquement les entites valide qui sont transmisent à
   * l'user.
   */
  public function filterToLoadEntities(AppEntityInterface $entity) {
    $status = false;
    if (GestionTache::userIsAdministrator()) {
      $status = true;
    }
    else {
      // si l'utlisateur n'a pas acces à l'entite de configuration, alors il n'a
      // aucun droit sur les entites creer à partir de cette derniere.
      if (!$this->checkAccessOnBundble($entity))
        throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour acceder à cette ressource ", 403);
      
      if (!$entity->IsPrivate())
        $status = true;
    }
    return $status;
  }
  
  /**
   * Verifie l'acces au niveau du bundle
   */
  protected function checkAccessOnBundble(\Drupal\Core\Entity\ContentEntityBase $entity) {
    if (is_null($this->bundleAcsess)) {
      if ($BundleEntityType = $entity->getEntityType()->getBundleEntityType()) {
        $query = $this->EntityTypeManager->getStorage($BundleEntityType)->getQuery();
        $query->condition('id', $entity->bundle());
        if (!$this->filterToLoadEntityConfig($query))
          $this->bundleAcsess = false;
        else {
          $ids = $query->execute();
          if ($ids)
            $this->bundleAcsess = true;
        }
      }
    }
    return $this->bundleAcsess;
  }
  
  /**
   * Permet de filtrer les requetes..
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   */
  public function filterToLoadEntityConfig(\Drupal\Core\Entity\Query\QueryInterface &$query) {
    $status = false;
    if (GestionTache::userIsAdministrator()) {
      $status = true;
    }
    elseif (GestionTache::userIsManager()) {
      $query->condition('private', false);
      $status = true;
    }
    elseif (GestionTache::userIsEmployee() || GestionTache::userIsPerformer()) {
      $queryOr = $query->orConditionGroup();
      $queryOr->condition('user_id', GestionTache::UserId());
      $queryOr->condition('users.*', [
        GestionTache::UserId()
      ], 'IN');
      $query->condition($queryOr);
      $status = true;
    }
    return $status;
  }
  
  /**
   * Permet de determiner si un utilisateur a le droit d'editer une entitée de
   * config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entityType
   */
  public function accessToDeleteEntityConfig(array &$new_entities, array $entities) {
    $status = false;
    $user_id = \Drupal::currentUser()->id();
    if (GestionTache::userIsAdministrator()) {
      $status = true;
      $new_entities = $entities;
    }
    elseif ((GestionTache::userIsEmployee() || GestionTache::userIsPerformer() || GestionTache::userIsManager())) {
      // on se rasure que tous les contenus donc l'user veut supprimer lui
      // appartienne.
      $new_entities = [];
      foreach ($entities as $entity) {
        if ($entity->getUserId() == $user_id) {
          $new_entities[] = $entity;
        }
      }
      $status = true;
    }
    return $status;
  }
  
  /**
   * Permet de determiner si un utilisateur a le droit d'editer une entité de
   * config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entityType
   */
  public function accessToEditEntityConfig(\Drupal\Core\Entity\EntityInterface $entityType) {
    $user_id = \Drupal::currentUser()->id();
    $status = false;
    if (GestionTache::userIsAdministrator())
      $status = true;
    elseif ((GestionTache::userIsEmployee() || GestionTache::userIsPerformer()) && $user_id == $entityType->getUserId())
      $status = true;
    elseif ((GestionTache::userIsManager()) && ($user_id == $entityType->getUserId() || ($user_id != $entityType->getUserId() && !$entityType->private)))
      $status = true;
    return $status;
  }
  
  /**
   * Permet de determiner si un utilisateur a le droit d'editer une entitée de
   * config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entityType
   */
  public function accessToSaveEntityConfig(\Drupal\Core\Entity\EntityInterface $entityType) {
    $status = false;
    if (GestionTache::userIsAdministrator())
      $status = true;
    elseif (GestionTache::userIsMemberOfGestionTache())
      $status = true;
    return $status;
  }
  
}