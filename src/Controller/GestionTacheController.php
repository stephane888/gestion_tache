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
    return new static($container->get('gestion_tache.api'), $container->get('query_ajax.insert_update'), $container->get('query_ajax.select'));
  }
  
  /**
   *
   * @param GestionProject $GestionProject
   * @param InsertUpdate $InsertUpdate
   * @param Select $Select
   */
  function __construct(GestionProject $GestionProject, InsertUpdate $InsertUpdate, Select $Select) {
    $this->GestionProject = $GestionProject;
    $this->InsertUpdate = $InsertUpdate;
    $this->Select = $Select;
  }
  
  /**
   * Permet d'ajouter et modifier les données.
   * Les tables qui peuvent contenir les données doivent etre dans
   * Load::ValidationInsert.
   * Chaque foix que cela est necessaire.
   *
   * @param Request $Request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function Save(Request $Request) {
    $inserts = Json::decode($Request->getContent());
    $this->GestionProject->Load->ValidationInsert($inserts);
    $this->GestionProject->Load->addUserIdGestionProjectContent($inserts);
    $configs = $this->InsertUpdate->buildInserts($inserts);
    return $this->reponse($configs, $this->InsertUpdate->AjaxStatus->getCode(), $this->InsertUpdate->AjaxStatus->getMessage());
  }
  
  /**
   *
   * @param Request $Request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function Select(Request $Request) {
    return $this->reponse($this->Select->select());
  }
  
  /**
   *
   * @param string $query_param
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
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
   *
   * @param int $uid
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function getUsers($uid = null) {
    /**
     *
     * @var \Symfony\Component\Serializer\Serializer $serializer
     */
    $serializer = \Drupal::service('serializer');
    if ($uid) {
      $user = \Drupal\user\Entity\User::load($uid);
      $data = $serializer->serialize($user, 'json', [
        'plugin_id' => 'entity'
      ]);
      return $this->reponse($data);
    }
    else {
      $query = \Drupal::entityQuery('user');
      $query->condition('status', 1);
      $uids = $query->execute();
      $users = \Drupal\user\Entity\User::loadMultiple($uids);
      $datas = [];
      foreach ($users as $user) {
        $datas[] = $user->toArray();
      }
      return $this->reponse($datas);
    }
  }
  
  /**
   * -
   */
  function SelectProjectType() {
    return $this->reponse($this->GestionProject->Load->SelectProjectType());
  }
  
  function selectdatas() {
    return $this->reponse($this->GestionProject->Load->selectdatas());
  }
  
  function selectTacheEnours() {
    return $this->reponse($this->GestionProject->Load->selectTacheEnours());
  }
  
  function selectProject() {
    return $this->reponse($this->GestionProject->Load->selectProject());
  }
  
  /**
   * Builds the response.
   */
  public function build() {
    $configs = [
      '#type' => "html_tag",
      '#tag' => 'section',
      '#value' => 'Gestion de tache',
      '#attributes' => [
        'id' => 'app'
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
  
  /**
   * --
   */
  public function manageUser(Request $request, int $idcontents, int $uid) {
    $method = $request->getMethod();
    if ($method == 'POST') {
      $insert = [
        'table' => 'gestion_project_executant',
        'fields' => [
          'idcontents' => $idcontents,
          'uid' => $uid
        ]
      ];
      $configs = $this->InsertUpdate->buildInserts([
        $insert
      ]);
      return $this->reponse($configs, $this->InsertUpdate->AjaxStatus->getCode(), $this->InsertUpdate->AjaxStatus->getMessage());
    }
    elseif ($method == 'DELETE') {
      $insert = [
        'table' => 'gestion_project_executant',
        'fields' => [],
        'action' => 'delete',
        'where' => [
          [
            'column' => 'idcontents',
            'value' => $idcontents
          ],
          [
            'column' => 'uid',
            'value' => $uid
          ]
        ]
      ];
      $configs = $this->InsertUpdate->buildInserts([
        $insert
      ]);
      return $this->reponse($configs, $this->InsertUpdate->AjaxStatus->getCode(), $this->InsertUpdate->AjaxStatus->getMessage());
    }
  }
  
  /**
   * -La sauvegarde/maj/delete se fait sur save update.
   */
  public function managePrime(Request $request, int $idcontents) {
    // $method = $request->getMethod();
    // if ($method == 'POST') {
    // $insert = [
    // 'table' => 'gestion_project_executant',
    // 'fields' => [
    // 'idcontents' => $idcontents,
    // 'uid' => $uid
    // ]
    // ];
    // $configs = $this->InsertUpdate->buildInserts([
    // $insert
    // ]);
    // return $this->reponse($configs,
    // $this->InsertUpdate->AjaxStatus->getCode(),
    // $this->InsertUpdate->AjaxStatus->getMessage());
    // }
    // elseif ($method == 'DELETE') {
    // $insert = [
    // 'table' => 'gestion_project_executant',
    // 'fields' => [],
    // 'action' => 'delete',
    // 'where' => [
    // [
    // 'column' => 'idcontents',
    // 'value' => $idcontents
    // ],
    // [
    // 'column' => 'uid',
    // 'value' => $uid
    // ]
    // ]
    // ];
    // $configs = $this->InsertUpdate->buildInserts([
    // $insert
    // ]);
    // return $this->reponse($configs,
    // $this->InsertUpdate->AjaxStatus->getCode(),
    // $this->InsertUpdate->AjaxStatus->getMessage());
    // }
  }
  
  public function UserTaches($uid) {
    return $this->reponse($this->GestionProject->Load->LoadTaches($uid));
  }
  
  public function LoadDatasByCustomRequest() {
    return $this->reponse($this->GestionProject->Load->LoadDatasByCustomRequest());
  }
  
  /**
   *
   * @param array|mixed $configs
   * @param int $code
   * @param string $message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
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
      $reponse->setStatusCode(401, " Vous n'avez pas acces à la ressource ");
      $reponse->setContent(" Vous n'avez pas acces à la ressource ");
      return $reponse;
    }
  }
  
  /**
   * Verifie que l'utilisateur à le droit d'acceder au contenu.
   */
  protected function checkUserPermission() {
    $permission = 'gestion_tache__reserve_content';
    $requiement = \Drupal::routeMatch()->getRouteObject()->getRequirements();
    if (!empty($requiement['_permission']))
      $permission = $requiement['_permission'];
    return \Drupal::currentUser()->hasPermission($permission);
  }
  
}
