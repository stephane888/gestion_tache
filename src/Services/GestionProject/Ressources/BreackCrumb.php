<?php
namespace Drupal\gestion_tache\Services\GestionProject\Ressources;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;

class BreackCrumb {

  protected $request;

  protected $Connection;

  protected $Load;

  private $requeteSimple = " c.idcontents, c.text, c.titre, c.created_at, c.update_at, c.type,
  h.idhierachie, h.idcontentsparent, h.ordre, h.level ";

  private $requete = " cf.idconfigs, cf.testconfigs ";

  function __construct(Connection $Connection, RequestStack $RequestStack, Load $Load)
  {
    $this->request = $RequestStack->getCurrentRequest();
    $this->Connection = $Connection;
    $this->Load = $Load;
  }

  public function getFilAriane(array $project, $results = [])
  {
    if (! empty($project['level'])) {
      $idcontentsparent = $project['idcontentsparent'];
      $request = "select $this->requeteSimple from {gestion_project_hierachie} as h
      INNER JOIN {gestion_project_contents} as c ON h.idcontents = c.idcontents
      WHERE ( c.idcontents = $idcontentsparent) ";
      $results[] = $data = $this->Connection->query($request)->fetch(\PDO::FETCH_ASSOC);
      $results = $this->getFilAriane($data, $results);
    }
    return $results;
  }

  public function getDatas()
  {
    try {
      $project = $this->Load->LoadContent($this->request->getContent());
      // return $project;
      return $this->getFilAriane($project);
    } catch (\Error $e) {
      return [
        'status' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTrace()
      ];
    }
  }
}