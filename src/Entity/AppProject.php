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
 * Defines the App project entity.
 *
 * @ingroup gestion_tache
 *
 * @ContentEntityType(
 *   id = "app_project",
 *   label = @Translation("App project"),
 *   bundle_label = @Translation("App project type"),
 *   handlers = {
 *     "storage" = "Drupal\gestion_tache\AppProjectStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\AppProjectListBuilder",
 *     "views_data" = "Drupal\gestion_tache\Entity\AppProjectViewsData",
 *     "translation" = "Drupal\gestion_tache\AppProjectTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\gestion_tache\Form\AppProjectForm",
 *       "add" = "Drupal\gestion_tache\Form\AppProjectForm",
 *       "edit" = "Drupal\gestion_tache\Form\AppProjectForm",
 *       "delete" = "Drupal\gestion_tache\Form\AppProjectDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\AppProjectHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\gestion_tache\AppProjectAccessControlHandler",
 *   },
 *   base_table = "app_project",
 *   data_table = "app_project_field_data",
 *   revision_table = "app_project_revision",
 *   revision_data_table = "app_project_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer app project entities",
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
 *     "canonical" = "/app/project/app_project/{app_project}",
 *     "add-page" = "/app/project/app_project/add",
 *     "add-form" = "/app/project/app_project/add/{app_project_type}",
 *     "edit-form" = "/app/project/app_project/{app_project}/edit",
 *     "delete-form" = "/app/project/app_project/{app_project}/delete",
 *     "version-history" = "/app/project/app_project/{app_project}/revisions",
 *     "revision" = "/app/project/app_project/{app_project}/revisions/{app_project_revision}/view",
 *     "revision_revert" = "/app/project/app_project/{app_project}/revisions/{app_project_revision}/revert",
 *     "revision_delete" = "/app/project/app_project/{app_project}/revisions/{app_project_revision}/delete",
 *     "translation_revert" = "/app/project/app_project/{app_project}/revisions/{app_project_revision}/revert/{langcode}",
 *     "collection" = "/app/project/app_project",
 *   },
 *   bundle_entity_type = "app_project_type",
 *   field_ui_base_route = "entity.app_project_type.edit_form"
 * )
 */
class AppProject extends EditorialContentEntityBase implements AppProjectInterface {
  
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
    // make the app_project owner the revision author.
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
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the App project entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setTranslatable(TRUE)->setDisplayOptions('view', [
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
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t('The name of the App project entity.'))->setRevisionable(TRUE)->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE)->setTranslatable(true);
    
    //
    $fields['type_project'] = BaseFieldDefinition::create('entity_reference')->setLabel(" Type de project ")->setDisplayOptions('form', [
      'type' => 'select2_entity_reference',
      'weight' => 5,
      'settings' => array(
        'match_operator' => 'CONTAINS',
        'size' => '10',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      )
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setSetting('handler_settings', [
      'target_bundles' => [
        'type_project' => 'type_project'
      ],
      'sort' => [
        'field' => 'name',
        'direction' => 'asc'
      ],
      'auto_create' => true,
      'auto_create_bundle' => ''
    ])->setSetting('target_type', 'taxonomy_term')->setSetting('handler', 'default:taxonomy_term')->setRevisionable(TRUE)->setCardinality(1);
    //
    $fields['private'] = BaseFieldDefinition::create('boolean')->setLabel(" privé ? ")->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => 3
    ])->setDisplayOptions('view', [])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setDefaultValue(false);
    //
    $fields['status_execution'] = BaseFieldDefinition::create('list_string')->setLabel(" Status execution ")->setDisplayOptions('form', [
      'type' => 'options_select',
      'weight' => 5,
      'settings' => array(
        'match_operator' => 'CONTAINS',
        'size' => '10',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      )
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setSettings([
      'allowed_values' => [
        'new' => 'Nouveau',
        'runing' => 'Encours',
        'end' => 'Terminée',
        'cancel' => 'Annulée'
      ]
    ]);
    
    $fields['duree'] = BaseFieldDefinition::create('daterange')->setLabel(t('Durée'))->setRevisionable(TRUE)->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    //
    $fields['executants'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Executants'))->setSetting('target_type', 'user')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setCardinality(-1);
    //
    $fields['primes'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Primes'))->setSetting('target_type', 'app_prime')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setCardinality(-1);
    //
    $fields['client'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Client'))->setSetting('target_type', 'app_client')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setCardinality(-1);
    
    $fields['project_manager'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Chef de projet'))->setSetting('target_type', 'user')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['description'] = BaseFieldDefinition::create('text_long')->setLabel(" Description ")->setDisplayOptions('form', [
      'type' => 'text_textarea',
      'weight' => 0
    ])->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'text_default',
      'weight' => 0
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true);
    
    $fields['description_cancel'] = BaseFieldDefinition::create('text_long')->setLabel(" Raison de la suppression ")->setDisplayOptions('form', [
      'type' => 'text_textarea',
      'weight' => 0
    ])->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'text_default',
      'weight' => 0
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true);
    
    //
    $fields['status']->setDescription(t('A boolean indicating whether the App project is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')->setLabel(t('Revision translation affected'))->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))->setReadOnly(TRUE)->setRevisionable(TRUE)->setTranslatable(TRUE);
    
    return $fields;
  }
  
}
