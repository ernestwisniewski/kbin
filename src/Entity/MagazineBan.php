<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\MagazineBanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: MagazineBanRepository::class)]
class MagazineBan
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'bans')]
    #[JoinColumn(nullable: false)]
    public ?Magazine $magazine;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public ?User $user;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public ?User $bannedBy;
    #[Column(type: 'text', length: 2048, nullable: true)]
    public ?string $reason = null;
    #[Column(type: 'datetimetz', nullable: true)]
    public ?\DateTimeInterface $expiredAt = null;
    #[OneToMany(mappedBy: 'ban', targetEntity: MagazineBanNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $notifications;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(
        Magazine $magazine,
        User $user,
        User $bannedBy,
        string $reason = null,
        \DateTimeInterface $expiredAt = null
    ) {
        $this->magazine = $magazine;
        $this->user = $user;
        $this->bannedBy = $bannedBy;
        $this->reason = $reason;
        $this->expiredAt = $expiredAt;
        $this->notifications = new ArrayCollection();

        $this->createdAtTraitConstruct();
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
