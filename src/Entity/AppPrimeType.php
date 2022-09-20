<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the App prime type entity.
 *
 * @ConfigEntityType(
 *   id = "app_prime_type",
 *   label = @Translation("App prime type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\AppPrimeTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\gestion_tache\Form\AppPrimeTypeForm",
 *       "edit" = "Drupal\gestion_tache\Form\AppPrimeTypeForm",
 *       "delete" = "Drupal\gestion_tache\Form\AppPrimeTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\AppPrimeTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "app_prime_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "app_prime",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/app_prime_type/{app_prime_type}",
 *     "add-form" = "/admin/structure/app_prime_type/add",
 *     "edit-form" = "/admin/structure/app_prime_type/{app_prime_type}/edit",
 *     "delete-form" = "/admin/structure/app_prime_type/{app_prime_type}/delete",
 *     "collection" = "/admin/structure/app_prime_type"
 *   }
 * )
 */
class AppPrimeType extends ConfigEntityBundleBase implements AppPrimeTypeInterface {

  /**
   * The App prime type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The App prime type label.
   *
   * @var string
   */
  protected $label;

}
