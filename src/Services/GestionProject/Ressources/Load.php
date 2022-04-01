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
  private $requeteSimple = " c.idcontents, c.text, c.titre, c.created_at, c.update_at, c.type, c.uid, c.lastupdateuid, c.privaty, 
  h.idhierachie, h.idcontentsparent, h.ordre, h.level";
  private $requete = " cf.idconfigs, cf.testconfigs ";
  private $requeteLoadCard = " ct.date_depart_proposer, ct.date_fin_proposer, ct.date_fin_reel, ct.status, ct.temps_pause, ct.raison ";
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
    $query = "select $champs from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
      WHERE ( h.idcontentsparent = $idcontents OR c.idcontents = $idcontents )
    ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  public function SelectProjectType() {
    $query = " select * from {gestion_project_type} limit 0,50 ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  public function selectdatas() {
    $where = $this->request->getContent();
    $uid = $this->user->id();
    $query = "";
    $query .= " select ";
    $query .= " c.idcontents, c.text, c.titre, c.created_at, ";
    $query .= " c.update_at, c.type, h.idhierachie, h.idcontentsparent, ";
    $query .= " h.ordre, h.level";
    $query .= " from ";
    $query .= " {gestion_project_hierachie} as h ";
    $query .= " INNER JOIN ";
    $query .= " {gestion_project_contents} as c ";
    $query .= " ON h.idcontents = c.idcontents ";
    $query .= " WHERE ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
    if (!empty($where)) {
      $query .= " AND ";
      $query .= $where;
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   *
   * @return Array
   */
  public function selectTacheEnours() {
    $where = $this->request->getContent();
    $uid = $this->user->id();
    $query = "";
    $query .= " select ";
    $query .= " c.idcontents, c.text, c.titre, c.created_at, ";
    $query .= " c.update_at, c.type, h.idhierachie, h.idcontentsparent, ";
    $query .= " h.ordre, h.level, ";
    $query .= " t.status, t.date_depart_proposer, t.date_fin_proposer, t.date_fin_reel ";
    $query .= " from ";
    $query .= " {gestion_project_hierachie} as h ";
    $query .= " INNER JOIN ";
    $query .= " {gestion_project_contents} as c ";
    $query .= " ON h.idcontents = c.idcontents ";
    $query .= " INNER JOIN ";
    $query .= " {gestion_project_times} as t ";
    $query .= " ON t.idcontents = c.idcontents ";
    $query .= " WHERE ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
    if (!empty($where)) {
      $query .= " AND ";
      $query .= $where;
    }
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   *
   * @return \Drupal\Core\Database\Connection
   */
  public function selectProject() {
    $where = $this->request->getContent();
    $uid = $this->user->id();
    $query = "";
    $query .= " select * from {gestion_project_contents} as c ";
    $query .= " WHERE ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
    if (!empty($where)) {
      $query .= " AND ";
      $query .= $where;
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
      "gestion_project_type"
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
    $query = "select $champs from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      WHERE ( c.idcontents = $idcontents )
    ";
    $query .= " AND ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
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
    $query = "select $champs from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
      LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
      WHERE ( c.idcontents = $idcontents )
    ";
    $query .= " AND ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
    // return $query;
    $project = $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    // on ajoute la liste de utilisateur dans la requete.
    if (!empty($project[0]))
      $project[0]['executant'] = $this->getUserExectutant($idcontents);
    if (!empty($this->filtre)) {
      $this->loadRCardList($idcontents, $project);
    }
    elseif (!empty($project)) {
      $deep = 0;
      $this->loadRCard($idcontents, $project, $deep);
    }
    return $project;
  }
  
  public function LoadTaches(int $uid) {
    $champs = $this->requeteLoadCard;
    $query = "select $champs from {gestion_project_contents} as c
      INNER JOIN {gestion_project_executant} as gpe ON c.idcontents = gpe.idcontents 
      INNER JOIN {gestion_project_hierachie} as h ON h.idcontents = c.idcontents
      LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
      LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
      WHERE gpe.uid = '" . $uid . "';
    ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * --
   */
  protected function getUserExectutant(int $idcontents) {
    $query = "select * from {gestion_project_executant} where idcontents = " . $idcontents;
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  protected function loadRCardList($idcontents, &$results) {
    $uid = $this->user->id();
    $champs = $this->requeteLoadCard;
    $request = "select $champs from {gestion_project_hierachie} as h
        INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
        LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
        LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
        WHERE ( h.idcontentsparent = $idcontents $this->filtre)
      ";
    $request .= " AND ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
    $project = $this->Connection->query($request)->fetchAll(\PDO::FETCH_ASSOC);
    if (!empty($project)) {
      foreach ($results as $key => $ligne) {
        if ($ligne['idcontents'] == $idcontents) {
          $results[$key]['cards'] = $project;
          foreach ($results[$key]['cards'] as $data) {
            $idcontents = $data['idcontents'];
            $this->loadRCardList($data['idcontents'], $results[$key]['cards']);
          }
        }
      }
    }
    else {
      $idcontents = false;
    }
  }
  
  protected function loadRCard($idcontents, &$results, $deep = 0) {
    $deep++;
    $uid = $this->user->id();
    if (self::deep >= $deep) {
      $champs = $this->requeteLoadCard;
      $request = "select $champs from {gestion_project_hierachie} as h
        INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
        LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
        LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
        WHERE ( h.idcontentsparent = $idcontents )
      ";
      $request .= " AND ( ( c.`uid` = '0' OR c.`uid` = '$uid' ) OR c.`privaty` = '0'  ) ";
      $project = $this->Connection->query($request)->fetchAll(\PDO::FETCH_ASSOC);
      if (!empty($project)) {
        foreach ($results as $key => $ligne) {
          if ($ligne['idcontents'] == $idcontents) {
            $results[$key]['cards'] = $project;
            foreach ($results[$key]['cards'] as $data) {
              $idcontents = $data['idcontents'];
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
  
}