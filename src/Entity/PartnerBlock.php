<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PartnerBlockRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;

#[ORM\Entity(repositoryClass: PartnerBlockRepository::class)]
class PartnerBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column]
    public string $url;

    #[ORM\Column]
    public string $imageUrl;

    #[Column(type: 'integer', nullable: true, options: ['default' => null])]
    public ?int $imageWidth = null;

    #[Column(type: 'integer', nullable: true, options: ['default' => null])]
    public ?int $imageHeight = null;

    #[ORM\Column]
    public bool $isActive = false;

    #[Column(type: 'datetimetz')]
    public ?\DateTime $lastActive;

    public function getId(): ?int
    {
        return $this->id;
    }
}
