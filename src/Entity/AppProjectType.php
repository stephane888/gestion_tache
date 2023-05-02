<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\user\Entity\User;
use Drupal\gestion_tache\GestionTache;
use Drupal\gestion_tache\ExceptionGestionTache;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the App project type entity.
 *
 * @ConfigEntityType(
 *   id = "app_project_type",
 *   label = @Translation("App project type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\AppProjectTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\gestion_tache\Form\AppProjectTypeForm",
 *       "edit" = "Drupal\gestion_tache\Form\AppProjectTypeForm",
 *       "delete" = "Drupal\gestion_tache\Form\AppProjectTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\AppProjectTypeHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\gestion_tache\AppProjectTypeAccessControlHandler",
 *   },
 *   config_prefix = "app_project_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "app_project",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "users",
 *     "user_id",
 *     "private"
 *   },
 *   links = {
 *     "canonical" = "/app/project/app_project_type/{app_project_type}",
 *     "add-form" = "/app/project/app_project_type/add",
 *     "edit-form" = "/app/project/app_project_type/{app_project_type}/edit",
 *     "delete-form" = "/app/project/app_project_type/{app_project_type}/delete",
 *     "collection" = "/app/project/app_project_type"
 *   }
 * )
 */
class AppProjectType extends ConfigEntityBundleBase implements AppProjectTypeInterface {
  
  function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
  }
  
  /**
   * The App project type ID.
   *
   * @var string
   */
  protected $id;
  
  /**
   * The App project type label.
   *
   * @var string
   */
  protected $label;
  
  /**
   * Petite description
   *
   * @var string
   */
  protected $description;
  
  /**
   * Contient la liste des utilisateurs limitées qui peuvent acceder à ces
   * données.
   *
   * @var array
   */
  protected $users = [];
  
  /**
   * Auteur du type de contenu.
   *
   * @var boolean
   */
  protected $user_id;
  
  /**
   * Permet de rendre un projet accessible uniquement à l'utilisateur encours et
   * à ceux qui sont explicement definit dans $users;
   * Cella permet à des roles ayant acces à tous les projets publics de ne pas y
   * avoir access.
   *
   * @var array
   */
  protected $private = false;
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Entity\EntityBase::loadMultiple()
   */
  public static function loadMultiple(array $ids = NULL) {
    // TODO Auto-generated method stub
    return parent::loadMultiple();
  }
  
  public function postCreate(EntityStorageInterface $storage) {
    parent::postCreate($storage);
    $this->user_id = \Drupal::currentUser()->id();
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Config\Entity\ConfigEntityBundleBase::preSave()
   */
  public function preSave(EntityStorageInterface $storage) {
    // TODO Auto-generated method stub
    parent::preSave($storage);
    /**
     *
     * @var \Drupal\gestion_tache\Services\Api\AccessEntitiesController $AccessEntitiesController
     */
    $AccessEntitiesController = \Drupal::service('gestion_tache_v2.access_entity_controller');
    $user_id = \Drupal::currentUser()->id();
    if ($this->isNew()) {
      if (!$AccessEntitiesController->accessToSaveEntityConfig($this))
        throw new ExceptionGestionTache("Vous n'avez pas les droits necessaires pour creer cette ressource ", 403);
      // on met à jour l'id de l'utilisateur.
      $this->user_id = $user_id;
    }
    else {
      if (!$AccessEntitiesController->accessToEditEntityConfig($this))
        throw new ExceptionGestionTache("Vous n'avez pas les droits necessaires pour modifier cette ressource ", 403);
      if (empty($this->user_id)) {
        $this->user_id = \Drupal::currentUser()->id();
      }
    }
  }
  
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    $new_entities = [];
    /**
     *
     * @var \Drupal\gestion_tache\Services\Api\AccessEntitiesController $AccessEntitiesController
     */
    $AccessEntitiesController = \Drupal::service('gestion_tache_v2.access_entity_controller');
    
    if (!$AccessEntitiesController->accessToDeleteEntityConfig($new_entities, $entities));
    throw new ExceptionGestionTache("Vous n'avez pas les droits necessaires pour supprimer cette ressource ", 403);
    parent::preDelete($storage, $new_entities);
  }
  
  /**
   * Recupere la liste des options.
   */
  public function getListOptionsUsers() {
    $users = [];
    foreach ($this->users as $uid) {
      $user = User::load($uid);
      $users[$uid] = $user->getDisplayName();
    }
    return $users;
  }
  
  /**
   *
   * @return integer
   */
  public function getUserId() {
    return $this->user_id;
  }
  
  /**
   *
   * @return boolean
   */
  public function getPrivate() {
    return $this->private;
  }
  
}
