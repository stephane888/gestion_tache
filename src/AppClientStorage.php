<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AppClientStorage extends SqlContentEntityStorage implements AppClientStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AppClientInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {app_client_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {app_client_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(AppClientInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {app_client_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('app_client_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
