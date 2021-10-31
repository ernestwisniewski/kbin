<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\MagazineBanRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MagazineBanRepository::class)
 */
class MagazineBan
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="bans")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Magazine $magazine;
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    public ?User $user;
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    public ?User $bannedBy;
    /**
     * @ORM\Column(type="text", length=2048, nullable=true)
     */
    public ?string $reason = null;
    /**
     * @ORM\Column(type="datetimetz", length=2048, nullable=true)
     */
    public ?DateTimeInterface $expiredAt = null;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    public function __construct(Magazine $magazine, User $user, User $bannedBy, ?string $reason = null, ?DateTimeInterface $expiredAt = null)
    {
        $this->magazine  = $magazine;
        $this->user      = $user;
        $this->bannedBy  = $bannedBy;
        $this->reason    = $reason;
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
