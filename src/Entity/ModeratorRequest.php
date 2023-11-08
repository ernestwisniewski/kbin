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

#[Entity]
#[Table]
#[UniqueConstraint(name: 'moderator_request_magazine_user_idx', columns: ['magazine_id', 'user_id'])]
class ModeratorRequest
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[ManyToOne(targetEntity: User::class, inversedBy: 'moderatorRequests')]
    #[JoinColumn(nullable: false)]
    public User $user;
    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'moderatorRequests')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public Magazine $magazine;
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    public function __construct(Magazine $magazine, User $user)
    {
        $this->magazine = $magazine;
        $this->user = $user;

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __sleep()
    {
        return [];
    }
}
