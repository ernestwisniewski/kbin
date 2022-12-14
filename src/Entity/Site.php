<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Site
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string')]
    public string $domain;

    #[Column(type: 'string')]
    public string $title;

    #[Column(type: 'text', nullable: true)]
    public ?string $description;

    #[Column(type: 'text', nullable: true)]
    public ?string $terms = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $privacyPolicy = null;

    #[Column(type: 'boolean', nullable: false)]
    public bool $enabled = true;

    #[Column(type: 'boolean', nullable: false)]
    public bool $registrationOpen = true;

    public function getId(): ?int
    {
        return $this->id;
    }
}
