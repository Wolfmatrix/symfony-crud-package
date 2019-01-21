<?php

namespace Wolfmatrix\RestApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ApiEvent extends Event
{
    const CODE = 'custom.event.home_page_event';

    protected $entity;

    protected $entityType;

    protected $action;

    public function __construct($entity, $entityType, $action)
    {
        $this->entity = $entity;
        $this->action = $action;
        $this->entityType = $entityType;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function getAction()
    {
        return $this->action;
    }
}