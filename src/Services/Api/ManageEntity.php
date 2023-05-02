<?php

namespace Drupal\gestion_tache\Services\Api;

use Drupal\gestion_tache\GestionTache;
use Drupal\gestion_tache\ExceptionGestionTache;

/**
 * --
 *
 * @author stephane
 *        
 */
class ManageEntity extends BaseApi {
  
  /**
   *
   * @var \Drupal\gestion_tache\Services\Api\AccessEntitiesController
   */
  protected $AccessEntitiesController;
  
  /**
   * --
   */
  function __construct(AccessEntitiesController $AccessEntitiesController) {
    $this->AccessEntitiesController = $AccessEntitiesController;
  }
  
  /**
   *
   * @param string $entity_type_id
   * @param string $id
   */
  public function loadProjet($entity_type_id, $id) {
    $data = [];
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
    $query->condition('id', $id);
    if (!$this->AccessEntitiesController->filterToLoadEntityConfig($query))
      throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour acceder à cette ressource ", 403);
    $ids = $query->execute();
    if ($ids) {
      $id_new = reset($ids);
      $entity = $this->entityTypeManager()->getStorage($entity_type_id)->load($id_new);
      $data = $entity->toArray();
      $data["statistiques"] = $this->countEntities($entity_type_id, $entity);
    }
    return $data;
  }
  
  /**
   * Charge les types de projets en function du droits de l'utilisateur.
   * On distingue 3 cas:
   * 1 - administrateur il voit tous les elements.
   */
  public function loadProjets() {
    $idTypes = [
      [
        'id' => 'app_project_type',
        'label' => 'Projets',
        'description' => 'projets plus',
        'entity_id' => 'app_project'
      ],
      [
        'id' => 'app_memos_type',
        'label' => 'Memos',
        'description' => 'projets plus',
        'entity_id' => 'app_memos'
      ]
    ];
    $types = [];
    foreach ($idTypes as $val) {
      $entity_type_id = $val['id'];
      $types[$entity_type_id] = $val;
      $types[$entity_type_id]['entities'] = [];
      $entities = [];
      /**
       *
       * @var \Drupal\Core\Entity\Query\QueryInterface $query
       */
      $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
      if (!$this->AccessEntitiesController->filterToLoadEntityConfig($query))
        throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour acceder à cette ressource ", 403);
      $ids = $query->execute();
      if ($ids) {
        $entities = $this->entityTypeManager()->getStorage($entity_type_id)->loadMultiple($ids);
      }
      foreach ($entities as $l => $entity) {
        $types[$entity_type_id]['entities'][$l] = $entity->toArray();
        $types[$entity_type_id]['entities'][$l]["statistiques"] = $this->countEntities($val['entity_id'], $entity);
      }
    }
    return $types;
  }
  
  /**
   * Permet de decompte les entites (total, effectuee bref en function des
   * status).
   *
   * @param string $entity_type_id
   */
  protected function countEntities($entity_type_id, \Drupal\Core\Entity\EntityInterface $entityType) {
    $statistiques = [];
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $statistiques['total'] = $query->count()->execute();
    return $statistiques;
  }
  
  /**
   *
   * Permet de creer ou mettre à jour les entitées.
   *
   * @param array $values
   * @param string $entity_type_id
   * @return \Drupal\Core\Entity\EntityInterface|NULL|\Drupal\Core\Entity\EntityInterface
   */
  public function saveEntity(array $values, string $entity_type_id) {
    $entity = $this->entityTypeManager()->getStorage($entity_type_id)->create($values);
    // On determine si l'entité existe deja.
    $id = $entity->id();
    if ($id) {
      $entityOld = $this->entityTypeManager()->getStorage($entity_type_id)->load($id);
      // MAJ
      if ($entityOld) {
        foreach ($values as $fiedName => $value) {
          // on ne met pas à jour l'ID du contenu , ni l'id de l'auteur.
          if ($fiedName != 'id' && $value != $id && $fiedName != 'user_id')
            $entityOld->set($fiedName, $value);
        }
        $entityOld->save();
        $this->setAjaxMessage($entity->label() . " a été mise à jour ");
        return $entityOld;
      }
    }
    // create new
    $entity->save();
    $this->setAjaxMessage($entity->label() . " a été crée ");
    return $entity;
  }
  
}