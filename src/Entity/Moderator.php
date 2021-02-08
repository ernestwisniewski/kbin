<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\ModeratorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="moderator_magazine_user_idx",
 *         columns={"magazine_id", "user_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=ModeratorRepository::class)
 */
class Moderator
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="moderatorTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="moderators")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private Magazine $magazine;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isOwner = false;

    public function __construct(Magazine $magazine, User $user, $isOwner = false)
    {
        $this->magazine  = $magazine;
        $this->user      = $user;
        $this->isOwner   = $isOwner;

        $magazine->getModerators()->add($this);
        $user->getModeratorTokens()->add($this);

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMagazine(): Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getIsOwner(): ?bool
    {
        return $this->isOwner;
    }

    public function setIsOwner(bool $isOwner): self
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}
