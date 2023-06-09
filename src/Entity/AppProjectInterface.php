<?php

namespace Drupal\gestion_tache\Entity;

/**
 * Provides an interface for defining App project entities.
 *
 * @ingroup gestion_tache
 */
interface AppProjectInterface extends AppEntityInterface {
  
  /**
   * Check status of project
   *
   * @return string Name of the App project.
   */
  public function getStatusExecution();
  
  /**
   * Check status of project
   *
   * @return string Name of the App project.
   */
  public function setStatusExecution($status);
  
}
