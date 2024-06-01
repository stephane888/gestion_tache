<?php

namespace Drupal\gestion_tache\Services\Api;

use Drupal\gestion_tache\ExceptionGestionTache;
use Drupal\gestion_tache\GestionTache;
use Query\Repositories\Utility as QueryUtility;

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
   * charge un type de projet.
   *
   * @param string $entity_type_id
   * @param string $id
   */
  public function loadProjetById($entity_type_id, $id) {
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
      // dump($data, $entity->getEntityTypeId());
      // $data["statistiques"] = $this->countEntities($entity_type_id, $entity);
    }
    return $data;
  }
  
  /**
   * NB: on limite à 300 en attendant de develloper la pagination en front.
   * Charge les types d'entité.
   * Possede un control d'acces.
   *
   * @param array $val
   * @param array $types
   * @param boolean $count
   * @param number $page
   * @param number $limit
   * @throws ExceptionGestionTache
   */
  protected function loadTypeEntity(array $val, array &$types, $count = true, $page = 0, $limit = 300, $filters = []) {
    $entity_type_id = $val['id'];
    $types[$entity_type_id] = $val;
    $types[$entity_type_id]['entities'] = [];
    $entities = [];
    /**
     *
     * @var \Drupal\Core\Entity\Query\QueryInterface $query
     */
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
    foreach ($filters as $filter) {
      $query->condition($filter['field'], $filter['value'], $filter['operator']);
    }
    $query->pager($limit);
    if (!$this->AccessEntitiesController->filterToLoadEntityConfig($query))
      throw new ExceptionGestionTache(" Vous n'avez pas les droits necessaires pour acceder à cette ressource ", 403);
    $ids = $query->execute();
    if ($ids) {
      $entities = $this->entityTypeManager()->getStorage($entity_type_id)->loadMultiple($ids);
    }
    foreach ($entities as $l => $entity) {
      $types[$entity_type_id]['entities'][$l] = $entity->toArray();
      if ($count)
        $types[$entity_type_id]['entities'][$l]["statistiques"] = $this->countEntities($val['entity_id'], $entity);
    }
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
      $this->loadTypeEntity($val, $types);
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
    // dump(\debug_backtrace());
    // dd($entity_type_id, $entityType);
    $statistiques = [];
    // total des taches crrer
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
    $query->accessCheck();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $statistiques['total'] = $query->count()->execute();
    // Taches validate.
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
    $query->accessCheck();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $query->condition('status_execution', 'validate');
    $statistiques['validate'] = $query->count()->execute();
    // Taches terminé
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery();
    $query->accessCheck();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $query->condition('status_execution', 'end');
    $statistiques['end'] = $query->count()->execute();
    // Montant
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getAggregateQuery();
    $query->accessCheck();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $query->condition('status_execution', 'cancel', '<>');
    $alias = 'montants';
    $query->aggregate('montant', 'sum', NULL, $alias);
    $alias = 'investissements';
    $query->aggregate('investissement', 'sum', NULL, $alias);
    $statistiques['montant'] = $query->execute();
    // Perte financiere.
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getAggregateQuery();
    $query->accessCheck();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $query->condition('status_execution', 'cancel', '=');
    $alias = 'montants';
    $query->aggregate('montant', 'sum', NULL, $alias);
    $alias = 'investissements';
    $query->aggregate('investissement', 'sum', NULL, $alias);
    $statistiques['pertes'] = $query->execute();
    // Duree_execution prevu.
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getAggregateQuery();
    $query->accessCheck();
    $query->condition('status', true);
    $query->condition('type', $entityType->id());
    $query->condition('duree_execution', 0, '>');
    $alias = 'duree_executions';
    $query->aggregate('duree_execution', 'sum', NULL, $alias);
    $statistiques['duree_execution'] = $query->execute();
    // Duree_execution reelle.
    /**
     * La durée d'execution reelle ce calcule sur les taches donc le status est
     * terminés ou validées.
     * Cette requete est assez complqiue pour pouvoir l'ecrire avec les APIs.
     */
    $type = $entityType->id();
    $query = "
    select SUM(UNIX_TIMESTAMP(apd.duree_value)) as durree_begin, SUM(UNIX_TIMESTAMP(apd.duree_end_value)) as duree_end,
    SUM(UNIX_TIMESTAMP(duree_end_value) - UNIX_TIMESTAMP(duree_value)) as duree
    from `app_project_field_data` as ap
    INNER JOIN `app_project__duree` apd ON apd.`entity_id`=ap.`id`
    WHERE ap.status = 1 and (ap.status_execution = 'validate' or ap.status_execution = 'end') and type = '$type'
    group by ap.id
";
    $result = \Drupal::database()->query($query);
    $result->execute();
    $statistiques['duree_execution_reelle'] = $result->fetchAll(\PDO::FETCH_ASSOC);
    return $statistiques;
  }
  
  /**
   * Les projets concernent l'entité app_project et sub_taches.
   * Logique :
   * 1- On charge tous les type de projets donc l'utilisateur a access.
   * 2- On charge les projets en fonction de ces types.
   * 3- On filtre le resultat en function des paramettres fournit.
   * 4-
   */
  function LoadMyTaches(array $filters, $uid) {
    if (!(GestionTache::userIsManager() || GestionTache::userIsAdministrator()))
      $uid = GestionTache::UserId();
    $val = [
      'id' => 'app_project_type',
      'label' => 'Projets',
      'description' => 'projets plus',
      'entity_id' => 'app_project'
    ];
    $typesProjects = [];
    $filter_types = [
      [
        'field' => 'status',
        'operator' => '=',
        'value' => true
      ],
      [
        'field' => 'users.*',
        'operator' => 'IN',
        'value' => [
          $uid
        ]
      ]
    ];
    $this->loadTypeEntity($val, $typesProjects, false, 0, 300, $filter_types);
    foreach ($typesProjects as $k => $value) {
      if (!empty($value['entities'])) {
        foreach ($value['entities'] as $id => $entity_bundle) {
          $typesProjects[$k]['entities'][$id]['entities_content'] = [];
          $query = $this->entityTypeManager()->getStorage($value['entity_id'])->getQuery();
          $query->accessCheck();
          $query->condition('type', $entity_bundle['id']);
          QueryUtility::buildFilterSqlForDrupal($filters, $query);
          $ids = $query->execute();
          if ($ids) {
            $nodes = $this->entityTypeManager()->getStorage($value['entity_id'])->loadMultiple($ids);
            foreach ($nodes as $node) {
              $ar = $node->toArray();
              $subtaches = $this->entityTypeManager()->getStorage("sub_tache")->loadByProperties([
                'app_project' => $node->id(),
                'status_execution' => 'new'
              ]);
              $ar['sub_taches'] = [];
              if ($subtaches) {
                foreach ($subtaches as $tache) {
                  $ar['sub_taches'][] = $tache->toArray();
                }
              }
              $typesProjects[$k]['entities'][$id]['entities_content'][] = $ar;
            }
          }
        }
      }
    }
    return $typesProjects;
  }
  
  /**
   *
   * Permet de creer ou mettre à jour les entitées.
   *
   * @param array $values
   * @param string $entity_type_id
   * @return \Drupal\Core\Entity\EntityInterface|NULL|\Drupal\Core\Entity\EntityInterface
   * @deprecated plus utilisé, on utilise le module apivuejs
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