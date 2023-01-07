<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\EmbedRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: EmbedRepository::class)]
#[Table]
#[UniqueConstraint(name: 'url_idx', columns: ['url'])]
class Embed
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Column(type: 'string', nullable: false)]
    public string $url;
    #[Column(type: 'boolean', nullable: false)]
    public bool $hasEmbed = false;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(string $url, bool $embed)
    {
        $this->url = $url;
        $this->hasEmbed = $embed;

        $this->createdAtTraitConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
