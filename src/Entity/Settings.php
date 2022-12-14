<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string', nullable: false)]
    public string $name;

    #[Column(type: 'string', nullable: true)]
    public ?string $value = null;

    public function getId(): int
    {
        return $this->id;
    }
}
