<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\MagazineSubscriptionRepository;
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Magazine::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Magazine $magazine;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

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

    public function __sleep()
    {
        return [];
    }
}
