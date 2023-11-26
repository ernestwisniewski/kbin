<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AwardRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: AwardRepository::class)]
class View
{
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public User $user;
    #[ManyToOne(targetEntity: Entry::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public Entry $entry;
    #[ManyToOne(targetEntity: Post::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public Post $post;
    #[Column(type: 'datetimetz')]
    public ?\DateTime $lastActive;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
