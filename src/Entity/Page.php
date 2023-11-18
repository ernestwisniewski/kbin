<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    public string $name;

    #[ORM\Column]
    public string $title;

    #[ORM\Column(type: 'text')]
    public string $body;

    #[ORM\Column(options: ['default' => 'en'])]
    public string $lang = 'en';

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;
}
