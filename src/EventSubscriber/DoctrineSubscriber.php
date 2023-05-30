<?php

namespace App\EventSubscriber;

use App\Entity\ApiToken;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DoctrineSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return array(Events::prePersist);
    }

    public function prePersist(PrePersistEventArgs $args)
    {
        $entity             = $args->getObject();
        //$entityManager      = $args->getObjectManager();


        if ($entity instanceof ApiToken)
        {
            $entity->addTimeToToken();
        }
    }
}