<?php

namespace Drupal\gestion_tache\Services\GestionProject\Ressources;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxy;

// use Drupal\user\UserAuthInterface;
class Search {
  protected $request;
  protected $Connection;
  protected $user;
  const queryKey = 'key';
  
  function __construct(Connection $Connection, RequestStack $RequestStack, AccountProxy $user) {
    $this->request = $RequestStack->getCurrentRequest();
    $this->Connection = $Connection;
    $this->user = $user;
  }
  
  public function search() {
    $queryKey = $this->request->query->get(self::queryKey);
    $uid = $this->user->id();
    // select * from contents WHERE `titre` LIKE '%Contient les modules de
    // comptabilités%' or `text` LIKE '%Contient les modules de comptabilités%'
    // LIMIT 50
    // ( (`uid` == '$uid' OR `uid` == '0') OR `privaty`=='0' ) AND
    $query = "select * from {gestion_project_contents} WHERE 
    ( ( (`uid` = '0' and `privaty` = '0') OR `uid` = '$uid' ) OR `privaty` = '0' )  AND  `titre` LIKE '%$queryKey%' LIMIT 50 ";
    return $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
  }
  
}