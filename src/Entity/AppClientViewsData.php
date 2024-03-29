<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for App client entities.
 */
class AppClientViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
