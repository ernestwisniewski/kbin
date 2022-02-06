<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\UserNoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="user_noted_idx",
 *         columns={"user_id", "target_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=UserNoteRepository::class)
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
class UserNote
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?User $user;
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?User $target;
    /**
     * @ORM\Column(type="text")
     */
    public string $body;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    public function __construct(User $user, User $target, string $body)
    {
        $this->createdAtTraitConstruct();

        $this->user   = $user;
        $this->target = $target;
        $this->body   = $body;
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
