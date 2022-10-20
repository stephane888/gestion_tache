<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the App client type entity.
 *
 * @ConfigEntityType(
 *   id = "app_client_type",
 *   label = @Translation("App client type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\AppClientTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\gestion_tache\Form\AppClientTypeForm",
 *       "edit" = "Drupal\gestion_tache\Form\AppClientTypeForm",
 *       "delete" = "Drupal\gestion_tache\Form\AppClientTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\AppClientTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "app_client_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "app_client",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/app_client_type/{app_client_type}",
 *     "add-form" = "/admin/structure/app_client_type/add",
 *     "edit-form" = "/admin/structure/app_client_type/{app_client_type}/edit",
 *     "delete-form" = "/admin/structure/app_client_type/{app_client_type}/delete",
 *     "collection" = "/admin/structure/app_client_type"
 *   }
 * )
 */
class AppClientType extends ConfigEntityBundleBase implements AppClientTypeInterface {
  
  /**
   * The App client type ID.
   *
   * @var string
   */
  protected $id;
  
  /**
   * The App client type label.
   *
   * @var string
   */
  protected $label;
  
}
