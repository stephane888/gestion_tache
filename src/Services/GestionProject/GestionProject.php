<?php
namespace Drupal\gestion_tache\Services\GestionProject;

use Drupal\gestion_tache\Services\GestionProject\Ressources\Search;
use Drupal\gestion_tache\Services\GestionProject\Ressources\Load;
use Drupal\gestion_tache\Services\GestionProject\Ressources\BreackCrumb;

class GestionProject {

  public $Search;

  public $Load;

  public $BreackCrumb;

  function __construct(Search $Search, Load $Load, BreackCrumb $BreackCrumb)
  {
    $this->Search = $Search;
    $this->Load = $Load;
    $this->BreackCrumb = $BreackCrumb;
  }
}