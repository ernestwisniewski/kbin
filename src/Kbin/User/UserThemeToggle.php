<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserThemeToggle
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user): void
    {
        $user->toggleTheme();

        $this->entityManager->flush();
    }
}
