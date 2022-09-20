<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the App memos type entity.
 *
 * @ConfigEntityType(
 *   id = "app_memos_type",
 *   label = @Translation("App memos type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\AppMemosTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\gestion_tache\Form\AppMemosTypeForm",
 *       "edit" = "Drupal\gestion_tache\Form\AppMemosTypeForm",
 *       "delete" = "Drupal\gestion_tache\Form\AppMemosTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\AppMemosTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "app_memos_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "app_memos",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/app_memos_type/{app_memos_type}",
 *     "add-form" = "/admin/structure/app_memos_type/add",
 *     "edit-form" = "/admin/structure/app_memos_type/{app_memos_type}/edit",
 *     "delete-form" = "/admin/structure/app_memos_type/{app_memos_type}/delete",
 *     "collection" = "/admin/structure/app_memos_type"
 *   }
 * )
 */
class AppMemosType extends ConfigEntityBundleBase implements AppMemosTypeInterface {

  /**
   * The App memos type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The App memos type label.
   *
   * @var string
   */
  protected $label;

}
