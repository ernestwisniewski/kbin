<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: 'App\Repository\UserFollowRepository')]
#[Table]
#[UniqueConstraint(name: 'user_follows_idx', columns: ['follower_id', 'following_id'])]
class UserFollow
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class, inversedBy: 'follows')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $follower;
    #[ManyToOne(targetEntity: User::class, inversedBy: 'followers')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $following;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(User $follower, User $following)
    {
        $this->createdAtTraitConstruct();

        $this->follower = $follower;
        $this->following = $following;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __sleep()
    {
        return [];
    }
}
