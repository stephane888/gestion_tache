<?php

namespace Drupal\gestion_tache\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\jsonapi\ResourceType\ResourceTypeBuildEvents;
use Drupal\jsonapi\ResourceType\ResourceTypeBuildEvent;

/**
 *
 * @author stephane
 * @see https://www.drupal.org/docs/core-modules-and-themes/core-modules/jsonapi-module/customizing-resources
 */
class GestionProjectV2Subscriber implements EventSubscriberInterface {
  
  function onResourceTypeBuild(ResourceTypeBuildEvent $event) {
    // add count meta datas.
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ResourceTypeBuildEvents::BUILD => [
        'onResourceTypeBuild'
      ]
    ];
  }
  
}