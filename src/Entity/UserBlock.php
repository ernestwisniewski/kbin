<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: 'App\Repository\UserBlockRepository')]
#[Table]
#[UniqueConstraint(name: 'user_block_idx', columns: ['blocker_id', 'blocked_id'])]
class UserBlock
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class, inversedBy: 'blocks')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $blocker;
    #[ManyToOne(targetEntity: User::class, inversedBy: 'blockers')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $blocked;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(User $blocker, User $blocked)
    {
        $this->createdAtTraitConstruct();

        $this->blocker = $blocker;
        $this->blocked = $blocked;
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
