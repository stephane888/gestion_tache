<?php
namespace App\Service\GestionProject\Traits;

use Query\Repositories\Utility as Bd_Utility;
use Query\WbuJsonDb;
use Stephane888\Debug\SymfonyDebug;

trait Connexion {

  protected $BD;

  private $keyBd = "databaseconfig";

  protected function connexion()
  {
    if ($this->BD) {
      return;
    }

    $key_bd = $this->request->headers->get($this->keyBd);
    SymfonyDebug::saveLogs([
      'token_bd' => $key_bd,
      'headers' => $this->request->headers->keys(),
      'body' => $this->request->getContent()
    ], 'GestionProject::Connexion');

    if (! empty($key_bd)) {
      $bdinfo = Bd_Utility::checkCredentiel($this->Config->DataBase(), $key_bd);
      $this->BD = new WbuJsonDb($bdinfo);
    } else {
      throw new \Error(" Paramettre de connexion non definit " . json_encode($this->request->headers->keys()));
    }
  }
}