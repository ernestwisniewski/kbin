<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Category;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategoryDelete
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
