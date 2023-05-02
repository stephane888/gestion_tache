<?php

namespace Drupal\gestion_tache;

use LogicException;

/**
 * Permet de traiter les erreurs rencontrer pendant le processus.
 *
 * @author stephane
 *        
 */
class ExceptionGestionTache extends LogicException implements \Throwable {
  
  // function __construct($message = null, $code = null, $previous = null) {
  // parent::__construct($message, $code, $previous);
  // }
} 