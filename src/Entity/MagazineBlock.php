<?php

namespace App\Entity;

use App\Repository\UserBlockRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="magazine_block_idx",
 *         columns={"user_id", "magazine_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=UserBlockRepository::class)
 */
class MagazineBlock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="blockedMagazines")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Magazine $magazine;

    public function __construct(User $user, Magazine $magazine)
    {
        $this->user     = $user;
        $this->magazine = $magazine;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(?Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}
