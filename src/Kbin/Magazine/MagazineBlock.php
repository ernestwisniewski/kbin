<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\User;
use App\Event\Magazine\MagazineBlockedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class MagazineBlock
{
    public function __construct(
        private MagazineUnsubscribe $magazineUnsubscribe,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): void
    {
        ($this->magazineUnsubscribe)($magazine, $user);

        $user->blockMagazine($magazine);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBlockedEvent($magazine, $user));
    }
}
