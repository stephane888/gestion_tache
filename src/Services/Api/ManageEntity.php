<?php

namespace Drupal\gestion_tache\Services\Api;

/**
 * --
 *
 * @author stephane
 *        
 */
class ManageEntity extends BaseApi {
  
  /**
   * Charge les types de projets en function du droits de l'utilisateur.
   */
  public function loadProjets() {
    $idTypes = [
      [
        'id' => 'app_project_type',
        'label' => 'Projets',
        'description' => 'projets plus'
      ],
      [
        'id' => 'app_memos_type',
        'label' => 'Memos',
        'description' => 'projets plus'
      ]
    ];
    $types = [];
    foreach ($idTypes as $val) {
      $entity_type_id = $val['id'];
      $types[$entity_type_id] = $val;
      $types[$entity_type_id]['entities'] = $this->entityTypeManager()->getStorage($entity_type_id)->loadMultiple();
      foreach ($types[$entity_type_id]['entities'] as $l => $entity) {
        $types[$entity_type_id]['entities'][$l] = $entity->toArray();
      }
    }
    return $types;
  }
  
  /**
   * *
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