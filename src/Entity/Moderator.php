<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="moderator_magazine_user_idx",
 *         columns={"magazine_id", "user_id"}
 *     )
 * })
 * @ORM\Entity()
 */
class Moderator
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="moderatorTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    public User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="moderators")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    public Magazine $magazine;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isOwner = false;
    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isConfirmed = false;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    public function __construct(Magazine $magazine, User $user, $isOwner = false, $isConfirmed = false)
    {
        $this->magazine    = $magazine;
        $this->user        = $user;
        $this->isOwner     = $isOwner;
        $this->isConfirmed = $isConfirmed;

        $magazine->moderators->add($this);
        $user->moderatorTokens->add($this);

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __sleep()
    {
        return [];
    }
}
