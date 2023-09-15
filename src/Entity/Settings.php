<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: SettingsRepository::class)]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
class Settings
{
    #[Column(type: 'string', nullable: false)]
    public string $name;
    #[Column(type: 'string', nullable: true)]
    public ?string $value = null;
    #[Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public ?array $json = null;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(string $name, string|array $value)
    {
        $this->name = $name;

        if (\is_array($value)) {
            $this->json = $value;
        } else {
            $this->value = $value;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }
}
