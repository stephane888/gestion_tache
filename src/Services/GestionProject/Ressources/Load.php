<?php
namespace Drupal\gestion_tache\Services\GestionProject\Ressources;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;
use Query\Repositories\Utility as WbuJsonDbUtility;

class Load {

  protected $request;

  protected $Connection;

  private $filtre = '';

  const deep = 3;

  const queryKey = 'key';

  private $requeteSimple = " c.idcontents, c.text, c.titre, c.created_at, c.update_at, c.type, 
  h.idhierachie, h.idcontentsparent, h.ordre, h.level";

  private $requete = " cf.idconfigs, cf.testconfigs ";

  private $requeteLoadCard = " ct.date_depart_proposer, ct.date_fin_proposer, ct.date_fin_reel, ct.status, ct.temps_pause, ct.raison ";

  function __construct(Connection $Connection, RequestStack $RequestStack)
  {
    $this->request = $RequestStack->getCurrentRequest();
    $this->Connection = $Connection;
    $this->requete = $this->requeteSimple . ', ' . $this->requete;
    $this->requeteLoadCard = $this->requete . ',' . $this->requeteLoadCard;
  }

  /**
   * Doit permettre de charger un projet
   *
   * @param int $idcontents
   * @return []
   */
  public function LoadProject(int $idcontents)
  {
    $champs = $this->requete;
    $query = "select $champs from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
      WHERE ( h.idcontentsparent = $idcontents OR c.idcontents = $idcontents )
    ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Doit permettre de charger un contenu
   *
   * @param int $idcontents
   * @return []
   */
  public function LoadContent(int $idcontents)
  {
    $champs = $this->requeteSimple;
    $query = "select $champs from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      WHERE ( c.idcontents = $idcontents )
    ";
    return $this->Connection->query($query)->fetch(\PDO::FETCH_ASSOC);
  }

  /**
   * charge un projet avec ses taches et ses sous taches.
   *
   * @param int $idcontents
   * @return array
   */
  public function LoadProjectGroupCards(int $idcontents)
  {
    $project = [];
    $champs = $this->requeteLoadCard;
    $params = Json::decode($this->request->getContent());
    $this->filtre = '';
    if (! empty($params)) {
      $filtre = WbuJsonDbUtility::buildFilterSql($params);
      if (! empty($filtre)) {
        $this->filtre = ' and ' . $filtre;
      }
    }
    $query = "select $champs from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
      LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
      WHERE ( c.idcontents = $idcontents )
    ";
    // return $query;
    $project = $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    if (! empty($this->filtre)) {
      $this->loadRCardList($idcontents, $project);
    } elseif (! empty($project)) {
      $deep = 0;
      $this->loadRCard($idcontents, $project, $deep);
    }
    return $project;
  }

  protected function loadRCardList($idcontents, &$results)
  {
    $champs = $this->requeteLoadCard;
    $request = "select $champs from {gestion_project_hierachie} as h
        INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
        LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
        LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
        WHERE ( h.idcontentsparent = $idcontents $this->filtre)
      ";
    $project = $this->Connection->query($request)->fetchAll(\PDO::FETCH_ASSOC);
    if (! empty($project)) {
      foreach ($results as $key => $ligne) {
        if ($ligne['idcontents'] == $idcontents) {
          $results[$key]['cards'] = $project;
          foreach ($results[$key]['cards'] as $data) {
            $idcontents = $data['idcontents'];
            $this->loadRCardList($data['idcontents'], $results[$key]['cards']);
          }
        }
      }
    } else {
      $idcontents = false;
    }
  }

  protected function loadRCard($idcontents, &$results, $deep = 0)
  {
    $deep ++;
    if (self::deep >= $deep) {
      $champs = $this->requeteLoadCard;
      $request = "select $champs from {gestion_project_hierachie} as h
        INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
        LEFT JOIN {gestion_project_configs} as cf ON cf.idcontents = c.idcontents
        LEFT JOIN {gestion_project_times} as ct ON ct.idcontents = c.idcontents
        WHERE ( h.idcontentsparent = $idcontents )
      ";
      $project = $this->Connection->query($request)->fetchAll(\PDO::FETCH_ASSOC);
      if (! empty($project)) {
        foreach ($results as $key => $ligne) {
          if ($ligne['idcontents'] == $idcontents) {
            $results[$key]['cards'] = $project;
            foreach ($results[$key]['cards'] as $data) {
              $idcontents = $data['idcontents'];
              $this->loadRCard($data['idcontents'], $results[$key]['cards'], $deep);
            }
          }
        }
      } else {
        $idcontents = false;
      }
    }
  }
}