<?php

namespace Drupal\gestion_tache\Services\GestionProject\Ressources;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;
use Query\Repositories\Utility as WbuJsonDbUtility;
use Drupal\Core\Session\AccountProxy;

class Load {
  protected $request;
  protected $Connection;
  private $filtre = '';
  const deep = 3;
  const queryKey = 'key';
  private $requeteSimple = " gpc.idcontents, gpc.text, gpc.titre, gpc.created_at, gpc.update_at, gpc.type, gpc.uid, gpc.lastupdateuid, gpc.privaty, 
  gph.idhierachie, gph.idcontentsparent, gph.ordre, gph.level";
  private $requete = " gpcf.idconfigs, gpcf.testconfigs ";
  private $requeteLoadCard = " gpt.date_depart_proposer, gpt.date_fin_proposer, gpt.date_fin_reel, gpt.status, gpt.temps_pause, gpt.raison, gpp.status as prime_status, gpp.montant as prime_montant ";
  protected $user;
  
  function __construct(Connection $Connection, RequestStack $RequestStack, AccountProxy $user) {
    $this->request = $RequestStack->getCurrentRequest();
    $this->Connection = $Connection;
    $this->requete = $this->requeteSimple . ', ' . $this->requete;
    $this->requeteLoadCard = $this->requete . ',' . $this->requeteLoadCard;
    $this->user = $user;
  }
  
  /**
   * Doit permettre de charger un projet
   *
   * @param int $idcontents
   * @return []
   */
  public function LoadProject(int $idcontents) {
    $champs = $this->requete;
    $query = "select $champs from {gestion_project_hierachie} as gph
      INNER JOIN {gestion_project_contents} as gpc ON gph.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_configs} as gpcf ON gpcf.idcontents = gpc.idcontents
      WHERE ( gph.idcontentsparent = $idcontents OR gpc.idcontents = $idcontents )
    ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  public function SelectProjectType() {
    $query = " select * from {gestion_project_type} limit 0,50 ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  public function selectdatas() {
    $where = null;
    $ortherQuery = null;
    $this->getRequestQuery($where, $ortherQuery);
    $uid = $this->user->id();
    $query = "";
    $query .= " select ";
    $query .= " gpc.idcontents, gpc.text, gpc.titre, gpc.created_at, ";
    $query .= " gpc.update_at, gpc.type, gph.idhierachie, gph.idcontentsparent, ";
    $query .= " gph.ordre, gph.level";
    $query .= " from ";
    $query .= " {gestion_project_hierachie} as gph ";
    $query .= " INNER JOIN ";
    $query .= " {gestion_project_contents} as gpc ";
    $query .= " ON gph.idcontents = gpc.idcontents ";
    $query .= " WHERE ( ( gpc.`uid` = '0' OR gpc.`uid` = '$uid' ) OR gpc.`privaty` = '0'  ) ";
    if ($where) {
      $query .= " AND ";
      $query .= $where;
    }
    if ($ortherQuery) {
      $query .= " " . $ortherQuery . " ";
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   *
   * @return Array
   */
  public function selectTacheEnours() {
    $where = null;
    $ortherQuery = null;
    $this->getRequestQuery($where, $ortherQuery);
    $uid = $this->user->id();
    $query = "";
    $query .= " select ";
    $query .= " gpc.idcontents, gpc.text, gpc.titre, gpc.created_at, ";
    $query .= " gpc.update_at, gpc.type, gph.idhierachie, gph.idcontentsparent, ";
    $query .= " gph.ordre, gph.level, ";
    $query .= " gpt.status, gpt.date_depart_proposer, gpt.date_fin_proposer, gpt.date_fin_reel ";
    $query .= " from ";
    $query .= " {gestion_project_hierachie} as gph ";
    $query .= " INNER JOIN ";
    $query .= " {gestion_project_contents} as gpc ";
    $query .= " ON gph.idcontents = gpc.idcontents ";
    $query .= " INNER JOIN ";
    $query .= " {gestion_project_times} as gpt ";
    $query .= " ON gpt.idcontents = gpc.idcontents ";
    $query .= " WHERE ( ( gpc.`uid` = '0' OR gpc.`uid` = '$uid' ) OR gpc.`privaty` = '0'  ) ";
    if ($where) {
      $query .= " AND ";
      $query .= $where;
    }
    if ($ortherQuery) {
      $query .= " " . $ortherQuery . " ";
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   *
   * @return \Drupal\Core\Database\Connection
   */
  public function selectProject() {
    $where = null;
    $ortherQuery = null;
    $this->getRequestQuery($where, $ortherQuery);
    $uid = $this->user->id();
    $query = "";
    $query .= " select * from {gestion_project_contents} as gpc ";
    $query .= " WHERE ( ( gpc.`uid` = '0' OR gpc.`uid` = '$uid' ) OR gpc.`privaty` = '0'  ) ";
    if ($where) {
      $query .= " AND ";
      $query .= $where;
    }
    if ($ortherQuery) {
      $query .= " " . $ortherQuery . " ";
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * --
   */
  public function ValidationInsert(array $inserts) {
    $tables = [
      "gestion_project_configs",
      "gestion_project_contents",
      "gestion_project_hierachie",
      "gestion_project_times",
      "gestion_project_type",
      "gestion_project_prime",
      "gestion_project_executant"
    ];
    foreach ($inserts as $value) {
      if (empty($value['table']) || !empty($tables[$value['table']])) {
        throw new \Exception(" vous n'avaez pas assez à cette table : " . $value['table']);
      }
    }
  }
  
  /**
   *
   * @param array $inserts
   */
  public function addUserIdGestionProjectContent(array &$inserts) {
    foreach ($inserts as $k => $value) {
      if (!empty($value['table']) && $value['table'] == 'gestion_project_contents') {
        if (!empty($value['action'])) {
          if ($value['action'] == 'update') {
            $inserts[$k]['fields']['lastupdateuid'] = $this->user->id();
          }
          else {
            $inserts[$k]['fields']['lastupdateuid'] = $this->user->id();
            $inserts[$k]['fields']['uid'] = $this->user->id();
          }
        }
        else {
          throw new \Exception(" Action non definit : " . $value['table']);
        }
      }
    }
  }
  
  /**
   * Doit permettre de charger un contenu
   *
   * @param int $idcontents
   * @return []
   */
  public function LoadContent(int $idcontents) {
    $champs = $this->requeteSimple;
    $uid = $this->user->id();
    $query = "select $champs from {gestion_project_hierachie} as gph
      INNER JOIN {gestion_project_contents} as gpc ON gph.idcontents = gpc.idcontents
      WHERE ( gpc.idcontents = $idcontents )
    ";
    $query .= " AND ( ( gpc.`uid` = '0' OR gpc.`uid` = '$uid' ) OR gpc.`privaty` = '0'  ) ";
    return $this->Connection->query($query)->fetch(\PDO::FETCH_ASSOC);
  }
  
  /**
   * charge un projet avec ses taches et ses sous taches.
   *
   * @param int $idcontents
   * @return array
   */
  public function LoadProjectGroupCards(int $idcontents) {
    $uid = $this->user->id();
    $project = [];
    $champs = $this->requeteLoadCard;
    $params = Json::decode($this->request->getContent());
    $this->filtre = '';
    if (!empty($params)) {
      $filtre = WbuJsonDbUtility::buildFilterSql($params);
      if (!empty($filtre)) {
        $this->filtre = ' and ' . $filtre;
      }
    }
    $query = "select $champs from {gestion_project_hierachie} as gph
      INNER JOIN {gestion_project_contents} as gpc ON gph.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_configs} as gpcf ON gpcf.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_times} as gpt ON gpt.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_prime} as gpp ON gpp.idcontents = gpc.idcontents
      WHERE ( gpc.idcontents = $idcontents )
    ";
    $query .= " AND 
     (
     /* une anciente tache qui a été rencu privé */
		    (gpc.`privaty` = '1' and gpc.`lastupdateuid`='$uid' and gpc.`uid`='0')		 
     /* Une nouvelle tache qui est privé */
		    OR (gpc.`privaty` = '1' and gpc.`uid`='$uid')
		 /* une tache à access public */
		    OR (gpc.`privaty` = '0')		 
	   )
  ";
    
    $project = $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    // on ajoute la liste de utilisateur dans la requete.
    if (!empty($project[0])) {
      $project[0]['executant'] = $this->getUserExectutant($idcontents);
    }
    
    if (!empty($this->filtre)) {
      $this->loadRCardList($idcontents, $project);
    }
    elseif (!empty($project)) {
      $deep = 0;
      $this->loadRCard($idcontents, $project, $deep);
    }
    return $project;
  }
  
  /**
   * Charge les taches des utilisateurs.
   *
   * @param int $uid
   */
  public function LoadTaches(int $uid) {
    $where = null;
    $ortherQuery = null;
    $this->getRequestQuery($where, $ortherQuery);
    $champs = $this->requeteLoadCard;
    $query = "select $champs from {gestion_project_contents} as gpc
      INNER JOIN {gestion_project_executant} as gpe ON gpc.idcontents = gpe.idcontents 
      INNER JOIN {gestion_project_hierachie} as gph ON gph.idcontents = gpc.idcontents
      INNER JOIN {gestion_project_times} as gpt ON gpt.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_configs} as gpcf ON gpcf.idcontents = gpc.idcontents      
      LEFT JOIN {gestion_project_prime} as gpp ON gpp.idcontents = gpc.idcontents
      WHERE gpe.uid = '" . $uid . "'  AND ( gpt.status = '0' OR gpt.status = '2' ) 
    ";
    if ($where) {
      $query .= " AND ";
      $query .= $where;
    }
    if ($ortherQuery) {
      $query .= " " . $ortherQuery . " ";
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * Permet au front de faire des filtres en function des differents cas.
   *
   * @param int $uid
   */
  public function LoadDatasByCustomRequest() {
    $where = null;
    $ortherQuery = null;
    $this->getRequestQuery($where, $ortherQuery);
    $uid = $this->user->id();
    
    $champs = $this->requeteLoadCard;
    $champs .= ', gpe.uid as executant_uid ';
    $query = "select $champs from {gestion_project_contents} as gpc
       INNER JOIN {gestion_project_executant} as gpe ON gpc.idcontents = gpe.idcontents 
      INNER JOIN {gestion_project_hierachie} as gph ON gph.idcontents = gpc.idcontents
      INNER JOIN {gestion_project_times} as gpt ON gpt.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_configs} as gpcf ON gpcf.idcontents = gpc.idcontents
      LEFT JOIN {gestion_project_prime} as gpp ON gpp.idcontents = gpc.idcontents
    ";
    $query .= "WHERE ( ( gpc.`uid` = '0' OR gpc.`uid` = '$uid' ) OR gpc.`privaty` = '0'  ) ";
    if ($where) {
      $query .= " AND ";
      $query .= $where;
    }
    if ($ortherQuery) {
      $query .= " " . $ortherQuery . " ";
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * --
   */
  protected function getUserExectutant(int $idcontents) {
    $query = "select * from {gestion_project_executant} where idcontents = " . $idcontents;
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * --
   */
  protected function getTachePrime(int $idcontents) {
    $query = " select * from {gestion_project_prime} where idcontents = " . $idcontents;
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  protected function loadRCardList($idcontents, &$results) {
    $uid = $this->user->id();
    $champs = $this->requeteLoadCard;
    $request = "select $champs from {gestion_project_hierachie} as gph
        INNER JOIN {gestion_project_contents} as gpc ON gph.idcontents = gpc.idcontents
        LEFT JOIN {gestion_project_configs} as gpcf ON gpcf.idcontents = gpc.idcontents
        LEFT JOIN {gestion_project_times} as gpt ON gpt.idcontents = gpc.idcontents
        LEFT JOIN {gestion_project_prime} as gpp ON gpp.idcontents = gpc.idcontents
        WHERE ( gph.idcontentsparent = $idcontents $this->filtre)
      ";
    $request .= " AND 
     (
     /* une anciente tache qui a été rencu privé */
		    (gpc.`privaty` = '1' and gpc.`lastupdateuid`='$uid' and gpc.`uid`='0')		 
     /* Une nouvelle tache qui est privé */
		    OR (gpc.`privaty` = '1' and gpc.`uid`='$uid')
		 /* une tache à access public */
		    OR (gpc.`privaty` = '0')		 
	   )

 ";
    $project = $this->Connection->query($request)->fetchAll(\PDO::FETCH_ASSOC);
    if (!empty($project)) {
      foreach ($results as $key => $ligne) {
        if ($ligne['idcontents'] == $idcontents) {
          $results[$key]['cards'] = $project;
          foreach ($results[$key]['cards'] as $k2 => $data) {
            $idcontents = $data['idcontents'];
            $results[$key]['cards'][$k2]['executant'] = $this->getUserExectutant($idcontents);
            $this->loadRCardList($data['idcontents'], $results[$key]['cards']);
          }
        }
      }
    }
    else {
      $idcontents = false;
    }
  }
  
  /**
   *
   * @param int $idcontents
   * @param array $results
   * @param number $deep
   */
  protected function loadRCard($idcontents, &$results, $deep = 0) {
    $deep++;
    $uid = $this->user->id();
    if (self::deep >= $deep) {
      $champs = $this->requeteLoadCard;
      $request = "select $champs from {gestion_project_hierachie} as gph
        INNER JOIN {gestion_project_contents} as gpc ON gph.idcontents = gpc.idcontents
        LEFT JOIN {gestion_project_configs} as gpcf ON gpcf.idcontents = gpc.idcontents
        LEFT JOIN {gestion_project_times} as gpt ON gpt.idcontents = gpc.idcontents
        LEFT JOIN {gestion_project_prime} as gpp ON gpp.idcontents = gpc.idcontents
        WHERE ( gph.idcontentsparent = $idcontents )
      ";
      $request .= " AND 
     (
     /* une anciente tache qui a été rencu privé */
		    (gpc.`privaty` = '1' and gpc.`lastupdateuid`='$uid' and gpc.`uid`='0')		 
     /* Une nouvelle tache qui est privé */
		    OR (gpc.`privaty` = '1' and gpc.`uid`='$uid')
		 /* une tache à access public */
		    OR (gpc.`privaty` = '0')		 
	   )
 ";
      $project = $this->Connection->query($request)->fetchAll(\PDO::FETCH_ASSOC);
      if (!empty($project)) {
        foreach ($results as $key => $ligne) {
          if ($ligne['idcontents'] == $idcontents) {
            $results[$key]['cards'] = $project;
            foreach ($results[$key]['cards'] as $k2 => $data) {
              $idcontents = $data['idcontents'];
              $results[$key]['cards'][$k2]['executant'] = $this->getUserExectutant($idcontents);
              $this->loadRCard($data['idcontents'], $results[$key]['cards'], $deep);
            }
          }
        }
      }
      else {
        $idcontents = false;
      }
    }
  }
  
  private function getRequestQuery(&$where, &$ortherQuery) {
    $datas = Json::decode($this->request->getContent());
    if (!empty($datas['where'])) {
      $where = $datas['where'];
    }
    if (!empty($datas['orther_query'])) {
      $ortherQuery = $datas['orther_query'];
    }
  }
  
}