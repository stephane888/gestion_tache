<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Sub tache entities.
 *
 * @ingroup gestion_tache
 */
class SubTacheListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Sub tache ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\gestion_tache\Entity\SubTache $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.sub_tache.edit_form',
      ['sub_tache' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
