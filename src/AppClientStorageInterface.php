<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\gestion_tache\Entity\AppClientInterface;

/**
 * Defines the storage handler class for App client entities.
 *
 * This extends the base storage class, adding required special handling for
 * App client entities.
 *
 * @ingroup gestion_tache
 */
interface AppClientStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of App client revision IDs for a specific App client.
   *
   * @param \Drupal\gestion_tache\Entity\AppClientInterface $entity
   *   The App client entity.
   *
   * @return int[]
   *   App client revision IDs (in ascending order).
   */
  public function revisionIds(AppClientInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as App client author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   App client revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\gestion_tache\Entity\AppClientInterface $entity
   *   The App client entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AppClientInterface $entity);

  /**
   * Unsets the language for all App client with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
