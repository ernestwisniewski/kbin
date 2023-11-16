<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\MagazineBan;
use App\Entity\User;
use App\Event\Magazine\MagazineBanEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class MagazineUnban
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): ?MagazineBan
    {
        if (!$magazine->isBanned($user)) {
            return null;
        }

        $ban = $magazine->unban($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBanEvent($ban));

        return $ban;
    }
}
