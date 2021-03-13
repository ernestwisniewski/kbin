<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\MagazineSubscriptionRepository;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="magazine_subsription_idx",
 *         columns={"user_id", "magazine_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=MagazineSubscriptionRepository::class)
 */
class MagazineSubscription
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Magazine $magazine;

    public function __construct(User $user, Magazine $magazine)
    {
        $this->createdAtTraitConstruct();
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
