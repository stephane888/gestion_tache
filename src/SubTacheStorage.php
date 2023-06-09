<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class SubTacheStorage extends SqlContentEntityStorage implements SubTacheStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(SubTacheInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {sub_tache_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {sub_tache_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(SubTacheInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {sub_tache_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('sub_tache_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
