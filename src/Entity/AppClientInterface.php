<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining App client entities.
 *
 * @ingroup gestion_tache
 */
interface AppClientInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the App client name.
   *
   * @return string
   *   Name of the App client.
   */
  public function getName();

  /**
   * Sets the App client name.
   *
   * @param string $name
   *   The App client name.
   *
   * @return \Drupal\gestion_tache\Entity\AppClientInterface
   *   The called App client entity.
   */
  public function setName($name);

  /**
   * Gets the App client creation timestamp.
   *
   * @return int
   *   Creation timestamp of the App client.
   */
  public function getCreatedTime();

  /**
   * Sets the App client creation timestamp.
   *
   * @param int $timestamp
   *   The App client creation timestamp.
   *
   * @return \Drupal\gestion_tache\Entity\AppClientInterface
   *   The called App client entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the App client revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the App client revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\gestion_tache\Entity\AppClientInterface
   *   The called App client entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the App client revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the App client revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\gestion_tache\Entity\AppClientInterface
   *   The called App client entity.
   */
  public function setRevisionUserId($uid);

}
