<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\User;
use App\Event\Magazine\MagazineSubscribedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class MagazineUnsubscribe
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        $magazine->unsubscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user, true));
    }
}
