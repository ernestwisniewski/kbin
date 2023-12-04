<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Magazine\EventSubscriber;

use App\Entity\MagazineLogBan;
use App\Kbin\Magazine\EventSubscriber\Event\MagazineBanEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class MagazineLogSubscriber
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: MagazineBanEvent::class)]
    public function onBan(MagazineBanEvent $event): void
    {
        $log = new MagazineLogBan($event->ban);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
