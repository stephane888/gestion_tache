<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of App prime entities.
 *
 * @ingroup gestion_tache
 */
class AppPrimeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('App prime ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\gestion_tache\Entity\AppPrime $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.app_prime.edit_form',
      ['app_prime' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
