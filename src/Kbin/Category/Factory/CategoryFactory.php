<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Category\Factory;

use App\Entity\Category;
use App\Kbin\Category\DTO\CategoryDto;

class CategoryFactory
{
    public function createDto(Category $category): CategoryDto
    {
        $categoryDto = new CategoryDto();
        $categoryDto->name = $category->name;
        $categoryDto->description = $category->description;
        $categoryDto->isPrivate = $category->isPrivate;
        $categoryDto->magazines = $category->getMagazines();

        return $categoryDto;
    }
}
