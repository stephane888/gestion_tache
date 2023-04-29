<?php

namespace Drupal\gestion_tache\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the App prime entity.
 *
 * @ingroup gestion_tache
 *
 * @ContentEntityType(
 *   id = "app_prime",
 *   label = @Translation("App prime"),
 *   bundle_label = @Translation("App prime type"),
 *   handlers = {
 *     "storage" = "Drupal\gestion_tache\AppPrimeStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\AppPrimeListBuilder",
 *     "views_data" = "Drupal\gestion_tache\Entity\AppPrimeViewsData",
 *     "translation" = "Drupal\gestion_tache\AppPrimeTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\gestion_tache\Form\AppPrimeForm",
 *       "add" = "Drupal\gestion_tache\Form\AppPrimeForm",
 *       "edit" = "Drupal\gestion_tache\Form\AppPrimeForm",
 *       "delete" = "Drupal\gestion_tache\Form\AppPrimeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\AppPrimeHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\gestion_tache\AppPrimeAccessControlHandler",
 *   },
 *   base_table = "app_prime",
 *   data_table = "app_prime_field_data",
 *   revision_table = "app_prime_revision",
 *   revision_data_table = "app_prime_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer app prime entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/app_prime/{app_prime}",
 *     "add-page" = "/admin/structure/app_prime/add",
 *     "add-form" = "/admin/structure/app_prime/add/{app_prime_type}",
 *     "edit-form" = "/admin/structure/app_prime/{app_prime}/edit",
 *     "delete-form" = "/admin/structure/app_prime/{app_prime}/delete",
 *     "version-history" = "/admin/structure/app_prime/{app_prime}/revisions",
 *     "revision" = "/admin/structure/app_prime/{app_prime}/revisions/{app_prime_revision}/view",
 *     "revision_revert" = "/admin/structure/app_prime/{app_prime}/revisions/{app_prime_revision}/revert",
 *     "revision_delete" = "/admin/structure/app_prime/{app_prime}/revisions/{app_prime_revision}/delete",
 *     "translation_revert" = "/admin/structure/app_prime/{app_prime}/revisions/{app_prime_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/app_prime",
 *   },
 *   bundle_entity_type = "app_prime_type",
 *   field_ui_base_route = "entity.app_prime_type.edit_form"
 * )
 */
class AppPrime extends EditorialContentEntityBase implements AppPrimeInterface {
  
  use EntityChangedTrait;
  use EntityPublishedTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id()
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    
    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    
    return $uri_route_parameters;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    
    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);
      
      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }
    
    // If no revision author has been set explicitly,
    // make the app_prime owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the App prime entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setTranslatable(TRUE)->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'author',
      'weight' => 0
    ])->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t('The name of the App prime entity.'))->setRevisionable(TRUE)->setSettings([
      'max_length' => 250,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['status']->setDescription(t('A boolean indicating whether the App prime is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')->setLabel(t('Revision translation affected'))->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))->setReadOnly(TRUE)->setRevisionable(TRUE)->setTranslatable(TRUE);
    
    return $fields;
  }
  
}
