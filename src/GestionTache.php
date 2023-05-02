<?php

namespace Drupal\gestion_tache;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

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
  static function getAvailableUserForProject(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, $cacheable = true) {
    $entity_type_id = $entity->getEntityType()->getBundleEntityType();
    /**
     *
     * @var \Drupal\gestion_tache\Entity\AppProjectType $entityType
     */
    $entityType = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity->bundle());
    return $entityType->getListOptionsUsers();
  }
  
  static function userConfigs($uid) {
    $confs = [];
    $user = \Drupal\user\Entity\User::load($uid);
    if ($user) {
      $confs['roles'] = self::roles();
      $confs['langue'] = $user->language()->getId();
    }
    return $confs;
  }
  
  /**
   * On distengue les roles suivant.
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