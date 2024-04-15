<?php

namespace Drupal\gestion_tache\Services\Api;

use Drupal\gestion_tache\GestionTache;
use Drupal\gestion_tache\ExceptionGestionTache;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Permet de recuprer les informations sur un ou plusieurs utilisateur.
 *
 * @author stephane
 *        
 */
class UserInfos extends BaseApi {
  
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
   * On doit verifier les droits d'acces afin de ne pas permettre la divulgation
   * d'information sensible.
   */
  public function getUsersRapports(array $filters, $page = 0, $number = 5000) {
    $uid = null;
    if (!GestionTache::userIsManager() && !GestionTache::userIsAdministrator()) {
      $uid = GestionTache::UserId();
    }
    $confs = [];
    $confs['timers_works'] = $this->getTimesByUser($uid, $filters, $page, $number);
    $confs['sqls'] = self::getSqls();
    $confs['filters'] = $filters;
    return $confs;
  }
  
  /**
   * On doit verifier les droits d'acces afin de ne pas permettre la divulgation
   * d'information sensible.
   */
  public function getUserInfos($uid) {
    if (GestionTache::UserId() !== $uid) {
      if (!GestionTache::userIsManager() && !GestionTache::userIsAdministrator()) {
        throw ExceptionGestionTache::exception("Vous n'avez pas les droits pour acceder à cette ressource.");
      }
    }
    $confs = [];
    $confs['timers_work_day'] = $this->getTimesByUser($uid);
    return $confs;
  }
  
  /**
   * Recupere la duree de travail du jour regrouper par projet.
   *
   * @param int $uid
   * @return array
   */
  protected function getTimesByUser($uid = null, array $filters = [], $page = 0, $number = 500) {
    $query = "
    select id, name,`type`, sum(duree) as duree from (
    select ap.id, apd.delta, ap.name, ap.`type`, 
    SUM(UNIX_TIMESTAMP(duree_end_value) - UNIX_TIMESTAMP(duree_value)) as duree
        from {app_project_field_data} as ap
        INNER JOIN {app_project__duree} apd ON apd.`entity_id`=ap.`id`
        WHERE ap.status = 1 and ( ap.status_execution = 'validate' or ap.status_execution = 'end' or ap.status_execution = 'break' )";
    if ($uid)
      $query .= " and ap.user_id = $uid ";
    if (!$filters) {
      $currentDay = date("Y-m-d");
      $query .= " and apd.duree_value LIKE  '$currentDay%' ";
    }
    else {
      foreach ($filters as $filter) {
        $query .= " and ";
        $query .= $filter['field_name'] . " ";
        $query .= $filter['operator'] . " ";
        $query .= $filter['value'] . " ";
      }
    }
    
    $query .= " group by apd.delta, ap.id ) as vbg
    group BY  id
    LIMIT $page,$number
    ";
    self::setSql('getTimesByUser', $query);
    $result = \Drupal::database()->query($query);
    $result->execute();
    $datas = $result->fetchAll(\PDO::FETCH_ASSOC);
    /**
     * On a besoin de classer les taches par projet, mais les noms de projets ne
     * sont pas facilement accessible, il est preferable de passer par l'API de
     * Drupal.
     */
    if ($datas) {
      $ids = [];
      foreach ($datas as $data) {
        $ids[] = $data['type'];
      }
      if ($ids) {
        $appProjectTypes = $this->EntityTypeManager->getStorage('app_project_type')->loadMultiple($ids);
      }
      foreach ($datas as $k => $data) {
        if (!empty($appProjectTypes[$data['type']])) {
          /**
           *
           * @var \Drupal\gestion_tache\Entity\AppProjectType $projectType
           */
          $projectType = $appProjectTypes[$data['type']];
          $datas[$k]['name_project'] = $projectType->label();
        }
        //
      }
    }
    return $datas;
  }
  
}