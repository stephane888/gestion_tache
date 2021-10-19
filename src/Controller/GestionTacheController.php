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
class GestionTacheController extends ControllerBase {
  protected $GestionProject;
  protected $InsertUpdate;
  protected $Select;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // return new static($container->get('prestashop_rest_api.cron'),
    // $container->get('prestashop_rest_api.build_product_to_drupal'));
    return new static($container->get('gestion_tache.api'),
        $container->get('query_ajax.insert_update'),
        $container->get('query_ajax.select'));
  }
  
  /**
   *
   * @param GestionProject $GestionProject
   * @param InsertUpdate $InsertUpdate
   * @param Select $Select
   */
  function __construct(GestionProject $GestionProject,
      InsertUpdate $InsertUpdate, Select $Select) {
    $this->GestionProject = $GestionProject;
    $this->InsertUpdate = $InsertUpdate;
    $this->Select = $Select;
  }
  
  /**
   * permet d'ajouter et modifier les données.
   *
   * @param Request $Request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function Save(Request $Request) {
    $inserts = Json::decode($Request->getContent());
    $configs = $this->InsertUpdate->buildInserts($inserts);
    return $this->reponse($configs, $this->InsertUpdate->AjaxStatus->getCode(),
        $this->InsertUpdate->AjaxStatus->getMessage());
  }
  
  function Select(Request $Request) {
    return $this->reponse($this->Select->select());
  }
  
  function CustomSelect($query_param) {
    $results = [];
    switch ($query_param) {
      case 'get-crumb':
        $results = $this->GestionProject->BreackCrumb->getDatas();
        break;
      
      default:
        ;
        break;
    }
    return $this->reponse($results);
  }
  
  /**
   * Builds the response.
   */
  public function build() {
    $configs = ['#type' => "html_tag", '#tag' => 'section',
      '#value' => 'Gestion de tache', '#attributes' => ['id' => 'app'
      ]
    ];
    $configs['#attached']['library'][] = 'gestion_tache/app_gestion_tache';
    return $configs;
  }
  
  public function Search() {
    $configs = $this->GestionProject->Search->search();
    return $this->reponse($configs);
  }
  
  public function LoadProject($id) {
    $configs = $this->GestionProject->Load->LoadProject($id);
    return $this->reponse($configs);
  }
  
  public function LoadProjectWithChildrens($id) {
    $configs = $this->GestionProject->Load->LoadProjectGroupCards($id);
    return $this->reponse($configs);
  }
  
  protected function reponse($configs, $code = null, $message = null) {
    $reponse = new JsonResponse();
    
    if ($this->checkUserPermission()) {
      if (!is_string($configs))
        $configs = Json::encode($configs);
      if ($code)
        $reponse->setStatusCode($code, $message);
      $reponse->setContent($configs);
      return $reponse;
    }
    else {
      $reponse->setStatusCode(401, "vous n'avez pas acces à la ressource");
      $reponse->setContent("vous n'avez pas acces à la ressource");
      return $reponse;
    }
  }
  
  /**
   * verifie que l'utilisateur à le droit d'acceder au contenu.
   */
  protected function checkUserPermission() {
    $permission = 'gestion_tache__reserve_content';
    $requiement = \Drupal::routeMatch()->getRouteObject()->getRequirements();
    if (!empty($requiement['_permission']))
      $permission = $requiement['_permission'];
    return \Drupal::currentUser()->hasPermission($permission);
  }
  
}
