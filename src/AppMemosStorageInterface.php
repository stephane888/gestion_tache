<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\gestion_tache\Entity\AppMemosInterface;

/**
 * Defines the storage handler class for App memos entities.
 *
 * This extends the base storage class, adding required special handling for
 * App memos entities.
 *
 * @ingroup gestion_tache
 */
interface AppMemosStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of App memos revision IDs for a specific App memos.
   *
   * @param \Drupal\gestion_tache\Entity\AppMemosInterface $entity
   *   The App memos entity.
   *
   * @return int[]
   *   App memos revision IDs (in ascending order).
   */
  public function revisionIds(AppMemosInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as App memos author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   App memos revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\gestion_tache\Entity\AppMemosInterface $entity
   *   The App memos entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AppMemosInterface $entity);

  /**
   * Unsets the language for all App memos with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
