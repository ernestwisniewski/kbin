<?php

namespace App\Entity;

use App\Repository\MagazineBanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MagazineBanRepository::class)
 */
class MagazineBan
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="bans")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Magazine $magazine;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $bannedBy;

    /**
     * @ORM\Column(type="text", length=2048, nullable=true)
     */
    private ?string $reason = null;

    public function __construct(Magazine $magazine, User $user, User $bannedBy, ?string $reason)
    {
        $this->magazine = $magazine;
        $this->user     = $user;
        $this->bannedBy = $bannedBy;
        $this->reason   = $reason;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBannedBy(): ?User
    {
        return $this->bannedBy;
    }

    public function setBannedBy(?User $bannedBy): self
    {
        $this->bannedBy = $bannedBy;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function __sleep()
    {
        return [];
    }
}
