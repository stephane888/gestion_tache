<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\gestion_tache\Entity\SubTacheInterface;

/**
 * Defines the storage handler class for Sub tache entities.
 *
 * This extends the base storage class, adding required special handling for
 * Sub tache entities.
 *
 * @ingroup gestion_tache
 */
interface SubTacheStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Sub tache revision IDs for a specific Sub tache.
   *
   * @param \Drupal\gestion_tache\Entity\SubTacheInterface $entity
   *   The Sub tache entity.
   *
   * @return int[]
   *   Sub tache revision IDs (in ascending order).
   */
  public function revisionIds(SubTacheInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Sub tache author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Sub tache revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\gestion_tache\Entity\SubTacheInterface $entity
   *   The Sub tache entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(SubTacheInterface $entity);

  /**
   * Unsets the language for all Sub tache with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
