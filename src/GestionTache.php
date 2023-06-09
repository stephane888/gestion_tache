<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\gestion_tache\Entity\AppEntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Datetime\DrupalDateTime;

class GestionTache {
  
  /**
   * Role par defaut definie par drupal.
   *
   * @var string
   */
  private static $administrator = 'administrator';
  
  /**
   *
   * @var string
   */
  private static $manager = 'manager';
  
  /**
   *
   * @var string
   */
  private static $employee = 'employee';
  
  /**
   *
   * @var string
   */
  private static $performer = 'performer';
  private static $roles;
  private static $UserId;
  
  /**
   * Permet d'ajouter l'uid dans le flux encours.
   * Le processus doit integrer l'authentification.
   * Cette function est utile pour les sauvegardes automatiques, (i.e quand le
   * sql est construit à distance ).
   */
  static function addCurrentUidOnfield() {
    //
  }
  
  static function defaultValueForFieldDate() {
    $date = new DrupalDateTime();
    // $format = $date->format("Y-m-d");
    // $format = "2023-05-03 10h";
    // dump($format);
    // return $format;
    return [
      // valeur qui seront sauvegarder en BD. ( YYYY-MM-DDTHH:mm:ss qui n'est
      // pas un format normalisé )
      'value' => $date->format('Y-m-d\TH:i:s'), //
      'end_value' => $date->format('Y-m-d\TH:i:s'),
      // pour le widget : daterange_default
      'end_date' => $date,
      'start_date' => $date
    ];
  }
  
  static function getAvailableUserForProjectByField(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, $cacheable = true) {
    return self::getAvailableUserForProject($entity);
  }
  
  static function getAvailableUserForProjectByEntityParent(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, $cacheable = true) {
    $id = $entity->get('app_project')->target_id;
    $parentEntity = \Drupal::entityTypeManager()->getStorage("app_project")->load($id);
    if ($parentEntity)
      return self::getAvailableUserForProject($parentEntity);
  }
  
  /**
   * Retourne la liste des utilisateurs en function du projets.
   * Explication:
   * Les utilisateurs sont selectionnées pendant la creation d'un projet et
   * pendant la creation d'une tache on peut associer un ou plusieurs
   * utilisateurs à une tache.
   *
   * @param FieldStorageDefinitionInterface $definition
   * @param FieldableEntityInterface $entity
   * @param boolean $cacheable
   * @return string[]|\Drupal\Component\Render\MarkupInterface[]
   */
  static function getAvailableUserForProject(AppEntityInterface $entity) {
    $entity_type_id = $entity->getEntityType()->getBundleEntityType();
    
    /**
     *
     * @var \Drupal\gestion_tache\Entity\AppProjectType $entityType
     */
    if ($entity_type_id) {
      $entityType = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity->bundle());
      return $entityType->getListOptionsUsers();
    }
    else {
      return [];
    }
  }
  
  /**
   * Retoune les configurations en relation avec un utilisateur.
   *
   * @param integer $uid
   * @return string[]|array[]
   */
  static function userConfigs($uid) {
    $confs = [];
    $user = \Drupal\user\Entity\User::load($uid);
    if ($user) {
      $confs['roles'] = self::roles();
      $confs['langue'] = $user->language()->getId();
      if ($uid == 1) {
        /**
         * Debut du journée : 02h.
         */
        $confs['pauses'] = [
          [
            7,
            8
          ],
          [
            12.5,
            14
          ],
          [
            18,
            19
          ]
        ];
        /**
         * Debut du journée : 02h.
         */
        $confs['day_duration'] = [
          2,
          21
        ];
        $confs['duration_work_day'] = $confs['day_duration'][1] - $confs['day_duration'][0] - self::sommePause($confs['pauses']);
      }
      else {
        $confs['pauses'] = [
          [
            13,
            14
          ]
        ];
        $confs['day_duration'] = [
          8,
          17
        ];
        $confs['duration_work_day'] = $confs['day_duration'][1] - $confs['day_duration'][0] - self::sommePause($confs['pauses']);
      }
      
      $confs['duration_pauses'] = self::sommePause($confs['pauses']);
      
      /**
       * Lundi à vendredi, doit etre dynamique car doit tenir compte des
       * feriers.
       */
      $confs['work_days'] = [
        1,
        2,
        3,
        4,
        5
      ];
    }
    return $confs;
  }
  
  protected static function sommePause($pauses) {
    $val = 0;
    foreach ($pauses as $pause) {
      $val += $pause[1] - $pause[0];
    }
    return $val;
  }
  
  /**
   * Essaie de determiner le chef de projet.
   * 1 - s'il ya un seul utilisateur, on l'assigne, s'ils sont plusiuers, on
   * laisse chacun choisir sa tache.
   *
   * @deprecated car simple pas necessaire, ( lorsque l'utilisateur clique sur
   *             le bouton start, il est definit comme executant), mais à
   *             suivre.
   */
  public static function ChiefManagerProject(AppEntityInterface $entity) {
    $uid = null;
    
    $users = self::getAvailableUserForProject($entity);
    if ($users) {
      if (count($users) == 1)
        return array_key_first($users);
    }
    return $uid;
  }
  
  /**
   * On distingue les roles suivant.
   * 1 : Manager
   * 2 : Employee
   * 3 : Performer
   * NB : Chaque role est independant des autres, donc le fait d'avoir le role
   * manager ne vous données pas acces au role Employee ou Performer.
   *
   * @see #2 (pour plus d'infos).
   */
  static function projetRoles() {
    return [
      self::$manager => self::$manager,
      self::$employee => self::$employee,
      self::$performer => self::$performer
    ];
  }
  
  /**
   * Determine si l'utilisateur a le role administrateur.
   *
   * @return boolean
   */
  public static function userIsAdministrator() {
    if (in_array(self::$administrator, self::roles()))
      return true;
    else
      false;
  }
  
  /**
   * Determine si l'utilisateur a le role manager.
   *
   * @return boolean
   */
  public static function userIsManager() {
    if (in_array(self::$manager, self::roles()))
      return true;
    else
      false;
  }
  
  /**
   * Determine si l'utilisateur a le role employee.
   *
   * @return boolean
   */
  public static function userIsEmployee() {
    if (in_array(self::$employee, self::roles()))
      return true;
    else
      false;
  }
  
  /**
   * Determine si l'utilisateur a le role performer.
   *
   * @return boolean
   */
  public static function userIsPerformer() {
    if (in_array(self::$performer, self::roles()))
      return true;
    else
      false;
  }
  
  public static function UserId() {
    if (!self::$UserId) {
      self::$UserId = \Drupal::currentUser()->id();
    }
    return self::$UserId;
  }
  
  /**
   * L'utilisateur est membre de gestion_tache.
   */
  public static function userIsMemberOfGestionTache() {
    if (self::userIsAdministrator())
      return true;
    $status = false;
    foreach (self::roles() as $role) {
      if (!empty(self::projetRoles()[$role])) {
        $status = true;
        break;
      }
    }
    return $status;
  }
  
  /**
   * Les roles de l'utlisateur encours.
   *
   * @return array
   */
  public static function roles() {
    if (!self::$roles) {
      self::$roles = \Drupal::currentUser()->getRoles();
    }
    return self::$roles;
  }
  
}