<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Category;

use App\Entity\Category;
use App\Kbin\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategoryOfficialToggle
{
    public function __construct(private Slugger $slugger, private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Category $category): Category
    {
        $category->isOfficial = !$category->isOfficial;

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }
}
