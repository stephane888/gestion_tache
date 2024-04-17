<?php

namespace Drupal\gestion_tache\Services\Api;

use Drupal\gestion_tache\GestionTache;
use Drupal\gestion_tache\ExceptionGestionTache;
use Drupal\Core\Entity\EntityTypeManager;
use Query\Repositories\Utility as QueryUtility;

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
  public function getUsersRapports(array $filters, $current_user_id, $page = 0, $number = 5000) {
    // Si l'utilisateur n'est pas admin or manager
    if (!(GestionTache::userIsManager() && GestionTache::userIsAdministrator())) {
      // Si le compte donc l'utilisateur essaie de voir est admin ou manager
      if (GestionTache::userIsManager($current_user_id) || GestionTache::userIsAdministrator($current_user_id)) {
        ExceptionGestionTache::exception(" Vous n'avez pas les acces necessaires pour effectuer cette tache ");
      }
    }
    $confs = [];
    $confs['timers_works'] = $this->getTimesByUser($current_user_id, $filters, $page, $number);
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
    $confs['sqls'] = self::getSqls();
    return $confs;
  }
  
  /**
   * Recupere la duree de travail du jour regrouper par projet.
   *
   * @param int $uid
   * @return array
   */
  protected function getTimesByUser($uid, array $filters = [], $page = 0, $number = 500) {
    $query = "
    select id, name,`type`, sum(duree) as duree from (
    select DISTINCT ap.id, apd.delta, ap.name, ap.`type`, 
    SUM(UNIX_TIMESTAMP(apd.duree_end_value) - UNIX_TIMESTAMP(apd.duree_value)) as duree
        from {app_project_field_data} as ap
        INNER JOIN {app_project__duree} apd ON apd.`entity_id`=ap.`id`
        LEFT JOIN {app_project__executants} ape ON ( ape.`entity_id`=ap.`id` and ape.executants_target_id = $uid )
        ";
    $Where = " WHERE ap.status = 1 and ( ap.status_execution = 'validate' or ap.status_execution = 'end' or ap.status_execution = 'break' ) ";
    
    // si le filtre n'est pas definit on l'execute pour l'utilisateur courant et
    // la journée en cours.
    if (!$filters) {
      $currentDay = date("Y-m-d");
      $Where .= " and ( ap.project_manager = $uid or ape.executants_target_id  = $uid )";
      $Where .= " and apd.duree_value LIKE  '$currentDay%' ";
    }
    else {
      QueryUtility::buildFilterSql($filters, $Where);
    }
    $query .= $Where;
    $query .= " group by apd.delta, ap.id ) as vbg
    group BY  id
    LIMIT $page,$number
    ";
    self::setSql('getTimesByUser', $query);
    // return [];
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