<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\gestion_tache\Services\Api\GestionProjectV2;
use Drupal\gestion_tache\Services\Api\UserInfos;
use Drupal\query_ajax\Services\InsertUpdate;
use Drupal\query_ajax\Services\Select;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\DrupalUtility\HttpResponse;
use Stephane888\Debug\ExceptionExtractMessage;
use Drupal\gestion_tache\GestionTache;
use Drupal\gestion_tache\ExceptionGestionTache;

/**
 * Returns responses for gestion tache routes.
 */
class GestionTacheV2Controller extends ControllerBase {
  protected $GestionProject;
  protected $EntityFieldManager;
  protected $UserInfos;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('gestion_tache_v2.api'), $container->get('entity_field.manager'), $container->get('gestion_tache_v2.user_infos'));
  }
  
  /**
   *
   * @param GestionProjectV2 $GestionProject
   * @param InsertUpdate $InsertUpdate
   * @param Select $Select
   */
  function __construct(GestionProjectV2 $GestionProject, EntityFieldManager $EntityFieldManager, UserInfos $UserInfos) {
    $this->GestionProject = $GestionProject;
    $this->EntityFieldManager = $EntityFieldManager;
    $this->UserInfos = $UserInfos;
  }
  
  /**
   * Permet de charger les taches de l'utilisateur encours et d'appliquer un
   * filtrer provenent du front.
   */
  function LoadMyTaches(Request $Request) {
    try {
      $filters = Json::decode($Request->getContent());
      $filters = $filters ? $filters : [];
      $datas = $this->GestionProject->ManageEntity->LoadMyTaches($filters);
      return HttpResponse::response($datas, $this->GestionProject->ManageEntity->getAjaxCode(), $this->GestionProject->ManageEntity->getAjaxMessage());
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Error $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
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
      return HttpResponse::response($datas, $this->GestionProject->ManageEntity->getAjaxCode(), $this->GestionProject->ManageEntity->getAjaxMessage());
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Error $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
  }
  
  /**
   * Permet de charger une entité, mais il faudra se rassurer qu'une entité est
   * chargé en respectant les droits.
   *
   * @param string $entity_type_id
   * @param string $id
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function LoadEntity($entity_type_id, $id) {
    try {
      $datas = $this->GestionProject->ManageEntity->loadProjetById($entity_type_id, $id);
      return HttpResponse::response($datas, $this->GestionProject->ManageEntity->getAjaxCode(), $this->GestionProject->ManageEntity->getAjaxMessage());
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '400', $e->getMessage());
    }
    catch (\Error $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '400', $e->getMessage());
    }
  }
  
  /**
   * Permet de mettre à jour ou de creer de nouvelles entitées de configuration.
   */
  public function saveEntities(Request $Request, $entity_type_id, $bundle) {
    try {
      $values = Json::decode($Request->getContent());
      $entity = $this->GestionProject->ManageEntity->saveEntity($values, $entity_type_id);
      $result = is_object($entity) ? $entity->toArray() : $entity;
      return HttpResponse::response($result, $this->GestionProject->ManageEntity->getAjaxCode(), $this->GestionProject->ManageEntity->getAjaxMessage());
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '400', $e->getMessage());
    }
    catch (\Error $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '400', $e->getMessage());
    }
  }
  
  /**
   * Recupere les informations de configurations.
   *
   * @param int $uid
   * @throws \Exception
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function userConfigs($uid) {
    try {
      if ($uid && \Drupal::currentUser()->id() != $uid)
        throw new \Exception("Paramettre de l'utilisateur incorect");
      return HttpResponse::response(GestionTache::userConfigs($uid));
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '435', $e->getMessage());
    }
    catch (\Error $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '435', $e->getMessage());
    }
  }
  
  /**
   * Recupere les informations utile pour l'utilisateur l'utilisateur
   *
   * @param int $uid
   */
  public function userInfos($uid) {
    try {
      return HttpResponse::response($this->UserInfos->getUserInfos($uid));
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '435', $e->getMessage());
    }
    catch (\Error $e) {
      return HttpResponse::response(GestionTache::userIsAdministrator() ? ExceptionExtractMessage::errorAll($e) : [], '435', $e->getMessage());
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
    return HttpResponse::response([]);
  }
  
  /**
   * Builds the response.
   * Recupere les champs pour un entité.
   */
  public function getForm($entity_type_id, $view_mode = 'default', $bundle = null, $entity = null) {
    try {
      /**
       *
       * @var \Drupal\Core\Config\Entity\ConfigEntityStorage $EntityStorage
       */
      $EntityStorage = $this->entityTypeManager()->getStorage($entity_type_id);
      // On determine si c'est un entity de configuration ou une entité de
      // contenu.
      // pour le moment, on peut differencier l'un de l'autre via la table de
      // base, seul les entités de contenus ont une table de base.
      /**
       *
       * @var \Drupal\Core\Config\Entity\ConfigEntityType $entityT
       */
      $entityT = $EntityStorage->getEntityType();
      if (!$entityT->getBaseTable()) {
        $entity_type_id = $entityT->getBundleOf();
        $EntityStorage = $this->entityTypeManager()->getStorage($entity_type_id);
      }
      if (empty($EntityStorage))
        throw new \Exception("Le type d'entité n'exsite pas : " . $entity_type_id);
      if (!$entity) {
        if ($bundle && $bundle != $entity_type_id)
          $entity = $EntityStorage->create([
            'type' => $bundle
          ]);
        else {
          $bundle = $entity_type_id;
          $entity = $EntityStorage->create();
        }
      }
      /**
       *
       * @var \Drupal\apivuejs\Services\GenerateForm $apivuejs
       */
      $apivuejs = \Drupal::service('apivuejs.getform');
      $res = $apivuejs->getForm($entity_type_id, $bundle, $view_mode, $entity);
      // dump($res);
      return HttpResponse::response([
        $res
      ]);
    }
    catch (ExceptionGestionTache $e) {
      $db = [];
      if (GestionTache::userIsAdministrator())
        $db = [
          'var_to_debug' => $e->getErrors(),
          'erros' => ExceptionExtractMessage::errorAll($e)
        ];
      return HttpResponse::response($db, !empty($e->getCode()) ? $e->getCode() : 432, $e->getMessage());
    }
    catch (\Exception $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 400, $e->getMessage());
    }
  }
  
}