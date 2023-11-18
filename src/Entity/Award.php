<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\AwardRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: AwardRepository::class)]
class Award
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class, inversedBy: 'awards')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public User $user;
    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'awards')]
    #[JoinColumn(onDelete: 'CASCADE')]
    public Magazine $magazine;
    #[ManyToOne(targetEntity: AwardType::class, inversedBy: 'awards')]
    #[JoinColumn(onDelete: 'CASCADE')]
    public AwardType $type;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;
}
