<?php

namespace Drupal\gestion_tache\Normalizer;

use Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer;

class EntityReferenceFieldDaterangeNormalizer extends EntityReferenceFieldItemNormalizer {
  
  public function normalize($field_item, $format = NULL, array $context = []) {
    $values = parent::normalize($field_item, $format, $context);
    dump($values);
    die("mon normalisateur kkksa888");
    return $values;
  }
  
}