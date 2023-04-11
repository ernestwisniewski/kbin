<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Site
{
    #[Column(type: 'text', nullable: true)]
    public ?string $terms = null;
    #[Column(type: 'text', nullable: true)]
    public ?string $privacyPolicy = null;
    #[Column(type: 'text', nullable: true)]
    public ?string $faq = null;
    #[Column(type: 'text', nullable: true)]
    public ?string $about = null;
    #[Column(type: 'text', nullable: true)]
    public ?string $contact = null;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
