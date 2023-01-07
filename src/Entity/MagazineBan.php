<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\MagazineBanRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: MagazineBanRepository::class)]
class MagazineBan
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'bans')]
    #[JoinColumn(nullable: false)]
    public ?Magazine $magazine;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public ?User $user;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public ?User $bannedBy;
    #[Column(type: 'text', length: 2048, nullable: true)]
    public ?string $reason = null;
    #[Column(type: 'datetimetz', nullable: true)]
    public ?\DateTimeInterface $expiredAt = null;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(
        Magazine $magazine,
        User $user,
        User $bannedBy,
        ?string $reason = null,
        ?\DateTimeInterface $expiredAt = null
    ) {
        $this->magazine = $magazine;
        $this->user = $user;
        $this->bannedBy = $bannedBy;
        $this->reason = $reason;
        $this->expiredAt = $expiredAt;

        $this->createdAtTraitConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __sleep()
    {
        return [];
    }
}
