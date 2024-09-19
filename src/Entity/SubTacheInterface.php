<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Sub tache entities.
 *
 * @ingroup gestion_tache
 */
interface SubTacheInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Sub tache name.
   *
   * @return string
   *   Name of the Sub tache.
   */
  public function getName();

  /**
   * Sets the Sub tache name.
   *
   * @param string $name
   *   The Sub tache name.
   *
   * @return \Drupal\gestion_tache\Entity\SubTacheInterface
   *   The called Sub tache entity.
   */
  public function setName($name);

  /**
   * Gets the Sub tache creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Sub tache.
   */
  public function getCreatedTime();

  /**
   * Sets the Sub tache creation timestamp.
   *
   * @param int $timestamp
   *   The Sub tache creation timestamp.
   *
   * @return \Drupal\gestion_tache\Entity\SubTacheInterface
   *   The called Sub tache entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Sub tache revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Sub tache revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\gestion_tache\Entity\SubTacheInterface
   *   The called Sub tache entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Sub tache revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Sub tache revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\gestion_tache\Entity\SubTacheInterface
   *   The called Sub tache entity.
   */
  public function setRevisionUserId($uid);

}
