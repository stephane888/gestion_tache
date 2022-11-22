<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\gestion_tache\Services\Api\GestionProjectV2;
use Drupal\query_ajax\Services\InsertUpdate;
use Drupal\query_ajax\Services\Select;
use Symfony\Component\HttpFoundation\Request;
use Drupal\gestion_tache\Entity\AppProject;
use Drupal\gestion_tache\Entity\AppProjectType;
use Stephane888\Debug\Utility as UtilityError;

/**
 * Returns responses for gestion tache routes.
 */
class GestionTacheV2Controller extends ControllerBase {
  protected $GestionProject;
  protected $EntityFieldManager;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('gestion_tache_v2.api'), $container->get('entity_field.manager'));
  }
  
  /**
   *
   * @param GestionProjectV2 $GestionProject
   * @param InsertUpdate $InsertUpdate
   * @param Select $Select
   */
  function __construct(GestionProjectV2 $GestionProject, EntityFieldManager $EntityFieldManager) {
    $this->GestionProject = $GestionProject;
    $this->EntityFieldManager = $EntityFieldManager;
  }
  
  /**
   * --
   */
  public function SelectProjectType() {
    return [];
  }
  
  /**
   * Builds the response.
   * Recupere les champs pour un entité.
   */
  public function getForm(Request $Request, $entity_type_id, $view_mode = 'default', $bundle = null, $entity = null) {
    /**
     * Fields storage.
     *
     * @var array $fields
     */
    $fields = $this->EntityFieldManager->getFieldStorageDefinitions($entity_type_id);
    //
    foreach ($array_expression as $value) {
      ;
    }
  }
  
  /**
   * Charge les projets en functions des droits des utilisateurs.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function LoadProjectType() {
    try {
      $datas = $this->GestionProject->ManageEntity->loadProjets();
      return $this->reponse($datas, $this->GestionProject->ManageEntity->getAjaxCode(), $this->GestionProject->ManageEntity->getAjaxMessage());
    }
    catch (\Exception $e) {
      return $this->reponse(UtilityError::errorAll($e), '400', $e->getMessage());
    }
    catch (\Error $e) {
      return $this->reponse(UtilityError::errorAll($e), '400', $e->getMessage());
    }
  }
  
  /**
   * Permet de mettre à jour ou de creer de nouveaux entitées.
   */
  public function saveEntities(Request $Request, $entity_type_id, $bundle) {
    try {
      $values = Json::decode($Request->getContent());
      $entity = $this->GestionProject->ManageEntity->saveEntity($values, $entity_type_id);
      $result = is_object($entity) ? $entity->toArray() : $entity;
      return $this->reponse($result, $this->GestionProject->ManageEntity->getAjaxCode(), $this->GestionProject->ManageEntity->getAjaxMessage());
    }
    catch (\Exception $e) {
      return $this->reponse(UtilityError::errorAll($e), '400', $e->getMessage());
    }
    catch (\Error $e) {
      return $this->reponse(UtilityError::errorAll($e), '400', $e->getMessage());
    }
  }
  
  /**
   * --
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function test() {
    $entity_type_id = 'app_project';
    /**
     *
     * @var \Drupal\gestion_tache\AppProjectStorage $app_project
     */
    $app_project = $this->entityTypeManager()->getStorage('app_project');
    /**
     *
     * @var \Drupal\Core\Entity\ContentEntityType
     */
    $fields = $this->EntityFieldManager->getFieldStorageDefinitions($entity_type_id);
    $values = [
      'type' => 'site_demo_m1'
    ];
    $entity = $this->entityTypeManager->getStorage('app_project')->create($values);
    // $dd = $this->entityFormBuilder()->getForm($entity);
    // dump($dd);
    return $this->reponse([]);
  }
  
  /**
   *
   * @param Array|string $configs
   * @param number $code
   * @param string $message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function reponse($configs, int $code = null, $message = null) {
    if (!is_string($configs))
      $configs = Json::encode($configs);
    $reponse = new JsonResponse();
    if ($code)
      $reponse->setStatusCode($code, $message);
    $reponse->setContent($configs);
    // utilise si on utilise le protocole, http/2
    // on authorise les navigateurs à afficher "CustomStatusText"
    $reponse->headers->set('Access-Control-Expose-Headers', "CustomStatusText");
    // Le protocole http/2.0 ne supporte pas le message sattus, alors on
    // le transfert via un entete personnalisé "CustomStatusText".
    $reponse->headers->set('CustomStatusText', $message);
    return $reponse;
  }
  
}