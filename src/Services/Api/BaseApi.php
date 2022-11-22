<?php

namespace Drupal\gestion_tache\Services\Api;

use Drupal\Core\Controller\ControllerBase;

/**
 * Permet de gerer les messages qui vont etre retouner.
 *
 * @author stephane
 *        
 */
class BaseApi extends ControllerBase {
  /**
   * message
   *
   * @var string
   */
  private $message = null;
  
  /**
   * code
   *
   * @var integer
   */
  private $code = 200;
  
  public function setAjaxMessage(string $value) {
    $this->message = $value;
  }
  
  public function SetAjaxCode(int $value) {
    $this->code = $value;
  }
  
  public function getAjaxMessage() {
    return $this->message;
  }
  
  public function getAjaxCode() {
    return $this->code;
  }
  
}