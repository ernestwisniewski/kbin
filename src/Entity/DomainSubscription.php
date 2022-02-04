<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\DomainSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="domain_subsription_idx",
 *         columns={"user_id", "domain_id"}
 *     )
 * })
 * @ORM\Entity(repositoryClass=DomainSubscriptionRepository::class)
 */
class DomainSubscription
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="subscribedDomains")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?User $user;
    /**
     * @ORM\ManyToOne(targetEntity=Domain::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Domain $domain;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    public function __construct(User $user, Domain $domain)
    {
        $this->createdAtTraitConstruct();
        $this->user   = $user;
        $this->domain = $domain;
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
