<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\CardanoTxRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: CardanoTxRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'ctx_type', type: 'text')]
#[DiscriminatorMap([
    'entry' => EntryCardanoTx::class,
])]
abstract class CardanoTx
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: Magazine::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    public Magazine $magazine;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn]
    public User $receiver;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn]
    public ?User $sender = null;
    #[Column(type: 'integer', nullable: false)]
    public int $amount = 0;
    #[Column(type: 'string', nullable: false)]
    public string $txHash;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(
        Magazine $magazine,
        int $amount,
        string $txHash,
        \DateTimeImmutable $createdAt,
        User $sender = null,
    ) {
        $this->magazine = $magazine;
        $this->sender = $sender;
        $this->amount = $amount;
        $this->txHash = $txHash;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): ContentInterface;

    abstract public function clearSubject(): self;
}
