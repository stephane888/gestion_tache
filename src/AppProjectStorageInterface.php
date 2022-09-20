<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\gestion_tache\Entity\AppProjectInterface;

/**
 * Defines the storage handler class for App project entities.
 *
 * This extends the base storage class, adding required special handling for
 * App project entities.
 *
 * @ingroup gestion_tache
 */
interface AppProjectStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of App project revision IDs for a specific App project.
   *
   * @param \Drupal\gestion_tache\Entity\AppProjectInterface $entity
   *   The App project entity.
   *
   * @return int[]
   *   App project revision IDs (in ascending order).
   */
  public function revisionIds(AppProjectInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as App project author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   App project revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\gestion_tache\Entity\AppProjectInterface $entity
   *   The App project entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AppProjectInterface $entity);

  /**
   * Unsets the language for all App project with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
