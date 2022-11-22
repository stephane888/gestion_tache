<?php

namespace Drupal\gestion_tache\Services\Api;

class GestionProjectV2 extends BaseApi {
  public $ManageEntity;
  
  /**
   *
   * @param ManageEntity $SaveEntity
   */
  function __construct(ManageEntity $ManageEntity) {
    $this->ManageEntity = $ManageEntity;
  }
  
}