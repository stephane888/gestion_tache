<?php

namespace Drupal\gestion_tache\Normalizer;

use Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer;

/**
 *
 * @see https://medium.com/@chris.geelhoed/how-to-alter-json-responses-with-drupal-8s-json-api-and-rest-web-service-7671f9c16658
 * @see https://drupal.stackexchange.com/questions/224351/is-there-any-way-to-alter-the-response-of-json-api-module
 * @see https://www.lullabot.com/articles/jsonapi-2
 *
 * @author stephane
 *        
 */
class EntityReferenceFieldDaterangeNormalizer extends EntityReferenceFieldItemNormalizer {
  
  public function normalize($field_item, $format = NULL, array $context = []) {
    $values = parent::normalize($field_item, $format, $context);
    dump($values);
    die("mon normalisateur kkksa888");
    return $values;
  }
  
}