<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\UserBlockRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_block_idx",
 *         columns={"blocker_id", "blocked_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=UserBlockRepository::class)
 */
class UserBlock
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blocks")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?User $blocker;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blockers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?User $blocked;

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

    public function getBlocker(): ?User
    {
        return $this->blocker;
    }

    public function setBlocker(?User $blocker): self
    {
        $this->blocker = $blocker;

        return $this;
    }

    public function getBlocked(): ?User
    {
        return $this->blocked;
    }

    public function setBlocked(?User $blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}
