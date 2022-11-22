<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

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
   * @var array
   */
  protected $user_id = [];
  
}
