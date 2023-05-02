<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining App project entities.
 *
 * @ingroup gestion_tache
 */
interface AppEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {
  
  /**
   * Add get/set methods for your configuration properties here.
   */
  
  /**
   * Check if entit i private.
   *
   * @return string Name of the App project.
   */
  public function IsPrivate();
  
  /**
   * Gets the App project name.
   *
   * @return string Name of the App project.
   */
  public function getName();
  
  /**
   * Sets the App project name.
   *
   * @param string $name
   *        The App project name.
   *        
   * @return \Drupal\gestion_tache\Entity\AppProjectInterface The called App
   *         project entity.
   */
  public function setName($name);
  
  /**
   * Gets the App project creation timestamp.
   *
   * @return int Creation timestamp of the App project.
   */
  public function getCreatedTime();
  
  /**
   * Sets the App project creation timestamp.
   *
   * @param int $timestamp
   *        The App project creation timestamp.
   *        
   * @return \Drupal\gestion_tache\Entity\AppProjectInterface The called App
   *         project entity.
   */
  public function setCreatedTime($timestamp);
  
  /**
   * Gets the App project revision creation timestamp.
   *
   * @return int The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();
  
  /**
   * Sets the App project revision creation timestamp.
   *
   * @param int $timestamp
   *        The UNIX timestamp of when this revision was created.
   *        
   * @return \Drupal\gestion_tache\Entity\AppProjectInterface The called App
   *         project entity.
   */
  public function setRevisionCreationTime($timestamp);
  
  /**
   * Gets the App project revision author.
   *
   * @return \Drupal\user\UserInterface The user entity for the revision author.
   */
  public function getRevisionUser();
  
  /**
   * Sets the App project revision author.
   *
   * @param int $uid
   *        The user ID of the revision author.
   *        
   * @return \Drupal\gestion_tache\Entity\AppProjectInterface The called App
   *         project entity.
   */
  public function setRevisionUserId($uid);
  
}
