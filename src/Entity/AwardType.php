<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AwardTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;

#[Entity(repositoryClass: AwardTypeRepository::class)]
class AwardType
{
    #[Column(type: 'string')]
    public string $name;
    #[Column(type: 'string')]
    public string $category;
    #[Column(type: 'integer', options: ['default' => 0])]
    public int $count = 0;
    #[Column(type: 'array', nullable: true)]
    public array $attributes;
    #[OneToMany(mappedBy: 'type', targetEntity: Award::class, fetch: 'EXTRA_LAZY')]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $awards;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }
}
