<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\DomainSubscriptionRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: DomainSubscriptionRepository::class)]
#[Table]
#[UniqueConstraint(name: 'domain_subscription_idx', columns: ['user_id', 'domain_id'])]
class DomainSubscription
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'subscribedDomains')]
    #[JoinColumn(nullable: false)]
    public ?User $user;

    #[ManyToOne(targetEntity: Domain::class, inversedBy: 'subscriptions')]
    #[JoinColumn(nullable: false)]
    public ?Domain $domain;

    public function __construct(User $user, Domain $domain)
    {
        $this->createdAtTraitConstruct();
        $this->user = $user;
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
