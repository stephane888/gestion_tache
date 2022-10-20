<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\gestion_tache\Services\GestionProject\GestionProject;
use Drupal\query_ajax\Services\InsertUpdate;
use Drupal\query_ajax\Services\Select;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for gestion tache routes.
 */
class GestionTacheV2Controller extends ControllerBase {
  protected $GestionProject;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('gestion_tache_v2.api'));
  }
  
  /**
   *
   * @param GestionProject $GestionProject
   * @param InsertUpdate $InsertUpdate
   * @param Select $Select
   */
  function __construct(GestionProject $GestionProject) {
    $this->GestionProject = $GestionProject;
  }
  
}