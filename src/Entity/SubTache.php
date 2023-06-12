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
 * Defines the Sub tache entity.
 *
 * @ingroup gestion_tache
 *
 * @ContentEntityType(
 *   id = "sub_tache",
 *   label = @Translation("Sub tache"),
 *   handlers = {
 *     "storage" = "Drupal\gestion_tache\SubTacheStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gestion_tache\SubTacheListBuilder",
 *     "views_data" = "Drupal\gestion_tache\Entity\SubTacheViewsData",
 *     "translation" = "Drupal\gestion_tache\SubTacheTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\gestion_tache\Form\SubTacheForm",
 *       "add" = "Drupal\gestion_tache\Form\SubTacheForm",
 *       "edit" = "Drupal\gestion_tache\Form\SubTacheForm",
 *       "delete" = "Drupal\gestion_tache\Form\SubTacheDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\gestion_tache\SubTacheHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\gestion_tache\SubTacheAccessControlHandler",
 *   },
 *   base_table = "sub_tache",
 *   data_table = "sub_tache_field_data",
 *   revision_table = "sub_tache_revision",
 *   revision_data_table = "sub_tache_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer sub tache entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/sub_tache/{sub_tache}",
 *     "add-form" = "/admin/structure/sub_tache/add",
 *     "edit-form" = "/admin/structure/sub_tache/{sub_tache}/edit",
 *     "delete-form" = "/admin/structure/sub_tache/{sub_tache}/delete",
 *     "version-history" = "/admin/structure/sub_tache/{sub_tache}/revisions",
 *     "revision" = "/admin/structure/sub_tache/{sub_tache}/revisions/{sub_tache_revision}/view",
 *     "revision_revert" = "/admin/structure/sub_tache/{sub_tache}/revisions/{sub_tache_revision}/revert",
 *     "revision_delete" = "/admin/structure/sub_tache/{sub_tache}/revisions/{sub_tache_revision}/delete",
 *     "translation_revert" = "/admin/structure/sub_tache/{sub_tache}/revisions/{sub_tache_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/sub_tache",
 *   },
 *   field_ui_base_route = "sub_tache.settings"
 * )
 */
class SubTache extends EditorialContentEntityBase implements AppProjectInterface {
  
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
    // make the sub_tache owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Entity\ContentEntityBase::postSave()
   */
  public function postSave(\Drupal\Core\Entity\EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    // TODO Auto-generated method stub
    /**
     * Lorsqu'on enregistre une sub_tache, on doit mettre à jour le status du
     * projet si la sous tache a un 'status_execution' = new|running
     */
    $status_execution = $this->getStatusExecution();
    if ($status_execution == 'new' || $status_execution == 'running') {
      /**
       *
       * @var \Drupal\gestion_tache\Entity\AppProject $projet
       */
      $projet = \Drupal::entityTypeManager()->getStorage('app_project')->load($this->getAppProject());
      if ($projet) {
        $status_projet = $projet->getStatusExecution();
        if ($status_projet != 'new' && $status_projet != 'running') {
          $projet->setStatusExecution('new');
          $projet->save();
        }
      }
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
  
  public function getStatusExecution() {
    return $this->get('status_execution')->value;
  }
  
  public function setStatusExecution($status) {
    return $this->set('status_execution', $status);
  }
  
  public function getAppProject() {
    return $this->get('app_project')->target_id;
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
  
  public function IsPrivate() {
    return $this->get('private')->value;
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
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Sub tache entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setTranslatable(TRUE)->setDisplayOptions('view', [
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
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t('The name of the Sub tache entity.'))->setRevisionable(TRUE)->setSettings([
      'max_length' => 255,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    //
    $fields['description'] = BaseFieldDefinition::create('text_long')->setLabel(" Description ")->setDisplayOptions('form', [
      'type' => 'text_textarea',
      'weight' => 0
    ])->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'text_default',
      'weight' => 0
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setRevisionable(TRUE);
    $fields['app_project'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Project'))->setSetting('target_type', 'app_project')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setCardinality(-1);
    $fields['status_execution'] = BaseFieldDefinition::create('list_string')->setLabel(" Status execution ")->setDisplayOptions('form', [
      'type' => 'options_buttons',
      'weight' => 5,
      'settings' => array(
        'match_operator' => 'CONTAINS',
        'size' => '10',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      )
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setSettings([
      'allowed_values' => [
        'new' => 'Nouvelle taches',
        'running' => "En cours d'execution",
        'end' => 'Terminée',
        'validate' => 'Validée',
        'cancel' => 'Annulée'
      ]
    ])->setRequired(true)->setDefaultValue('new');
    $fields['duree_execution'] = BaseFieldDefinition::create('integer')->setLabel(" Durée d'execution (mn) ")->setRevisionable(TRUE)->setSettings([
      'min' => 0
    ])->setDefaultValue(15)->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'number'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['project_manager'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Chef de projet'))->setDisplayOptions('form', [
      'type' => 'options_select',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '10',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setSetting('allowed_values_function', [
      '\Drupal\gestion_tache\GestionTache',
      'getAvailableUserForProjectByEntityParent'
    ])->setSetting('target_type', 'user')->setSetting('handler', 'default')->setDefaultValueCallback("\Drupal\gestion_tache\GestionTache::ChiefManagerProject");
    //
    $fields['duree'] = BaseFieldDefinition::create('daterange')->setLabel(t('Durée'))->setRevisionable(TRUE)->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setDisplayOptions('form', [
      'type' => 'daterange_default',
      'weight' => 0,
      'settings' => [
        'date_type' => 'date',
        'time_type' => 'time'
      ]
    ])->setRequired(TRUE)->setDefaultValueCallback('\Drupal\gestion_tache\GestionTache::defaultValueForFieldDate');
    
    //
    $fields['private'] = BaseFieldDefinition::create('boolean')->setLabel(" privé ? ")->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => 3
    ])->setDisplayOptions('view', [])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setDefaultValue(false);
    //
    $fields['status']->setDescription(t('A boolean indicating whether the Sub tache is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')->setLabel(t('Revision translation affected'))->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))->setReadOnly(TRUE)->setRevisionable(TRUE)->setTranslatable(TRUE);
    
    return $fields;
  }
  
}
