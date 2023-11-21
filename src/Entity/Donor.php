<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DonorRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: DonorRepository::class)]
class Donor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: true)]
    public ?User $user = null;

    #[ORM\Column(length: 255)]
    public string $username;

    #[ORM\Column(length: 255)]
    public string $email;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $url = null;

    #[ORM\Column]
    public bool $isActive = false;

    public function getId(): ?int
    {
        return $this->id;
    }
}
