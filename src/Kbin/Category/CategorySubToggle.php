<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Category;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategorySubToggle
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Category $category, User $user): void
    {
        $category->isSubscribed($user) ? $category->unsubscribe($user) : $category->subscribe($user);

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
}
