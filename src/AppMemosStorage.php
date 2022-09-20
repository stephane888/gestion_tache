<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AppMemosStorage extends SqlContentEntityStorage implements AppMemosStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AppMemosInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {app_memos_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {app_memos_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(AppMemosInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {app_memos_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('app_memos_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
