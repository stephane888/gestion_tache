<?php
namespace Drupal\gestion_tache\Services\GestionProject\Ressources;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;

class Search {

  protected $request;

  protected $Connection;

  const queryKey = 'key';

  function __construct(Connection $Connection, RequestStack $RequestStack)
  {
    $this->request = $RequestStack->getCurrentRequest();
    $this->Connection = $Connection;
  }

  public function search()
  {
    $queryKey = $this->request->query->get(self::queryKey);
    // select * from contents WHERE `titre` LIKE '%Contient les modules de comptabilités%' or `text` LIKE '%Contient les modules de comptabilités%' LIMIT 50
    $query = "select * from {gestion_project_contents} WHERE `titre` LIKE '%$queryKey%' LIMIT 50 ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
}