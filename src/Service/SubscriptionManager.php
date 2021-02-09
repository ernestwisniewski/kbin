<?php declare(strict_types=1);

namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\MagazineSubscribedEvent;
use App\Entity\Magazine;
use App\Entity\User;

class SubscriptionManager
{
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;

    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    public function subscribe(Magazine $magazine, User $user)
    {
        $magazine->subscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
    }

    public function unsubscribe(Magazine $magazine, User $user)
    {
        $magazine->unsubscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
    }
}
