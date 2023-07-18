<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\CardanoTxInitRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: CardanoTxInitRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'cpi_type', type: 'text')]
#[DiscriminatorMap([
    'entry' => EntryCardanoTxInit::class,
])]
abstract class CardanoTxInit
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: Magazine::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public Magazine $magazine;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: true)]
    public ?User $user = null;
    #[Column(type: 'string', nullable: false)]
    public string $sessionId;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(Magazine $magazine, string $sessionId, User $user = null)
    {
        $this->user = $user;
        $this->magazine = $magazine;
        $this->sessionId = $sessionId;

        $this->createdAtTraitConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): ContentInterface;

    abstract public function clearSubject(): self;
}
