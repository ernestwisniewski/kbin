<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\User;
use App\Event\Magazine\MagazineBlockedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class MagazineUnblock
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        $user->unblockMagazine($magazine);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBlockedEvent($magazine, $user));
    }
}
