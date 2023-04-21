<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: MagazineSubscription::class)]
#[Table]
#[UniqueConstraint(name: 'magazine_subscription_requests_idx', columns: ['user_id', 'magazine_id'])]
class MagazineSubscriptionRequest
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $user;
    #[ManyToOne(targetEntity: Magazine::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?Magazine $magazine;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(User $user, Magazine $magazine)
    {
        $this->createdAtTraitConstruct();

        $this->user = $user;
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
