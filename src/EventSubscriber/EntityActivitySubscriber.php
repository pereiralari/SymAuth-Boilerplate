<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class EntityActivitySubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!method_exists($entity, 'setCreatedBy')) {
            return;
        }

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $entity->setCreatedBy($user);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!method_exists($entity, 'setUpdatedBy')) {
            return;
        }

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $entity->setUpdatedBy($user);
        }
    }
}