<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\Badge;

use App\Entity\Badge;
use Doctrine\ORM\EntityManagerInterface;

class EntryBadgePurge
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Badge $badge): void
    {
        $this->entityManager->remove($badge);
        $this->entityManager->flush();
    }
}
