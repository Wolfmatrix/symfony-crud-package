<?php

namespace Wolfmatrix\RestApiBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wolfmatrix\RestApiBundle\Event\ApiEvent;

class ApiEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            ApiEvent::CODE => 'onCustomEvent',
        );
    }

    public function onCustomEvent(ApiEvent $event)
    {
        // fetch event information here
        dump(
            [
                'entityType' => $event->getEntityType(),
                'action' => $event->getAction(),
                'entity' => $event->getEntity()
            ]
        );
    }
}