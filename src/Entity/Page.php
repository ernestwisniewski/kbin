<?php

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
