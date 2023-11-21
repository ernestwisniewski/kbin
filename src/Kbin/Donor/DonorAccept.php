<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Donor;

use App\Entity\Donor;
use Doctrine\ORM\EntityManagerInterface;

readonly class DonorAccept
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Donor $donor): void
    {
        $donor->isActive = true;

        $this->entityManager->persist($donor);
        $this->entityManager->flush();
    }
}
