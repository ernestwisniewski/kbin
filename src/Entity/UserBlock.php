<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_block_idx",
 *         columns={"blocker_id", "blocked_id"}
 *     )
 * })
 * @ORM\Entity()
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
class UserBlock
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blocks")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?User $blocker;
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blockers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?User $blocked;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

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
