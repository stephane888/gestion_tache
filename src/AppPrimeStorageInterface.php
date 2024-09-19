<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\gestion_tache\Entity\AppPrimeInterface;

/**
 * Defines the storage handler class for App prime entities.
 *
 * This extends the base storage class, adding required special handling for
 * App prime entities.
 *
 * @ingroup gestion_tache
 */
interface AppPrimeStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of App prime revision IDs for a specific App prime.
   *
   * @param \Drupal\gestion_tache\Entity\AppPrimeInterface $entity
   *   The App prime entity.
   *
   * @return int[]
   *   App prime revision IDs (in ascending order).
   */
  public function revisionIds(AppPrimeInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as App prime author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   App prime revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\gestion_tache\Entity\AppPrimeInterface $entity
   *   The App prime entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AppPrimeInterface $entity);

  /**
   * Unsets the language for all App prime with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
