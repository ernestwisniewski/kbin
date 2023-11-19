<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\BrokenInstanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: BrokenInstanceRepository::class)]
#[UniqueConstraint(name: 'broken_instance_url_idx', columns: ['host'])]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class BrokenInstance
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    public string $host;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $exception = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
