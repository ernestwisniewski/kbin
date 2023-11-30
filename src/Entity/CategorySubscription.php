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
#[UniqueConstraint(name: 'category_subscription_idx', columns: ['user_id', 'category_id'])]
class CategorySubscription
{
    #[ManyToOne(targetEntity: User::class, inversedBy: 'subscribedCategories')]
    #[JoinColumn(nullable: false)]
    public ?User $user;
    #[ManyToOne(targetEntity: Category::class, inversedBy: 'subscriptions')]
    #[JoinColumn(nullable: false)]
    public ?Category $category;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(User $user, Category $category)
    {
        $this->user = $user;
        $this->category = $category;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
