<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining App memos entities.
 *
 * @ingroup gestion_tache
 */
interface AppMemosInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the App memos name.
   *
   * @return string
   *   Name of the App memos.
   */
  public function getName();

  /**
   * Sets the App memos name.
   *
   * @param string $name
   *   The App memos name.
   *
   * @return \Drupal\gestion_tache\Entity\AppMemosInterface
   *   The called App memos entity.
   */
  public function setName($name);

  /**
   * Gets the App memos creation timestamp.
   *
   * @return int
   *   Creation timestamp of the App memos.
   */
  public function getCreatedTime();

  /**
   * Sets the App memos creation timestamp.
   *
   * @param int $timestamp
   *   The App memos creation timestamp.
   *
   * @return \Drupal\gestion_tache\Entity\AppMemosInterface
   *   The called App memos entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the App memos revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the App memos revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\gestion_tache\Entity\AppMemosInterface
   *   The called App memos entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the App memos revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the App memos revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\gestion_tache\Entity\AppMemosInterface
   *   The called App memos entity.
   */
  public function setRevisionUserId($uid);

}
