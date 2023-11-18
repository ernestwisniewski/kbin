<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\MagazineSubscriptionRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: MagazineSubscriptionRepository::class)]
#[Table]
#[UniqueConstraint(name: 'magazine_subsription_idx', columns: ['user_id', 'magazine_id'])]
class MagazineSubscription
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class, inversedBy: 'subscriptions')]
    #[JoinColumn(nullable: false)]
    public ?User $user;
    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'subscriptions')]
    #[JoinColumn(nullable: false)]
    public ?Magazine $magazine;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(User $user, Magazine $magazine)
    {
        $this->createdAtTraitConstruct();
        $this->user = $user;
        $this->magazine = $magazine;
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
