<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Category;

use App\Entity\Category;
use App\Entity\User;
use App\Kbin\Category\DTO\CategoryDto;
use App\Kbin\Utils\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategoryCreate
{
    public function __construct(private Slugger $slugger, private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(CategoryDto $dto, User $user): Category
    {
        $category = new Category();

        $category->user = $user;
        $category->name = $dto->name;
        $category->description = $dto->description;
        $category->isPrivate = $dto->isPrivate;
        $category->slug = $this->slugger->slug($dto->name);

        $category->magazines = new ArrayCollection();
        foreach ($dto->magazines as $magazine) {
            $category->addMagazine($magazine);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }
}
