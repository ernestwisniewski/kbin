<?php declare(strict_types = 1);

namespace App\Entity;

use App\Repository\ModeratorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ModeratorRepository::class)
 */
class Moderator
{
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

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    private \DateTimeImmutable $createdAt;

    public function __construct(Magazine $magazine, User $user, $isOwner = false)
    {
        $this->magazine  = $magazine;
        $this->user      = $user;
        $this->isOwner   = $isOwner;
        $this->createdAt = new \DateTimeImmutable('@'.time());
        $magazine->getModerators()->add($this);
        $user->getModeratorTokens()->add($this);
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
