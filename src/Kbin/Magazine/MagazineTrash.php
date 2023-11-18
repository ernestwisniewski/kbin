<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use Doctrine\ORM\EntityManagerInterface;

readonly class MagazineTrash
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine): void
    {
        $magazine->trash();

        $this->entityManager->flush();
    }
}
