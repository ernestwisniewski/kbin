<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table]
#[UniqueConstraint(name: 'category_magazine_idx', columns: ['magazine_id', 'category_id'])]
class CategoryMagazine
{
    public function __construct(Category $category, Magazine $magazine)
    {
        $this->category = $category;
        $this->magazine = $magazine;
    }

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'categories')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?Magazine $magazine;
    #[ManyToOne(targetEntity: Category::class, inversedBy: 'magazines')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?Category $category;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
