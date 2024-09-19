<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining App prime entities.
 *
 * @ingroup gestion_tache
 */
interface AppPrimeInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the App prime name.
   *
   * @return string
   *   Name of the App prime.
   */
  public function getName();

  /**
   * Sets the App prime name.
   *
   * @param string $name
   *   The App prime name.
   *
   * @return \Drupal\gestion_tache\Entity\AppPrimeInterface
   *   The called App prime entity.
   */
  public function setName($name);

  /**
   * Gets the App prime creation timestamp.
   *
   * @return int
   *   Creation timestamp of the App prime.
   */
  public function getCreatedTime();

  /**
   * Sets the App prime creation timestamp.
   *
   * @param int $timestamp
   *   The App prime creation timestamp.
   *
   * @return \Drupal\gestion_tache\Entity\AppPrimeInterface
   *   The called App prime entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the App prime revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the App prime revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\gestion_tache\Entity\AppPrimeInterface
   *   The called App prime entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the App prime revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the App prime revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\gestion_tache\Entity\AppPrimeInterface
   *   The called App prime entity.
   */
  public function setRevisionUserId($uid);

}
