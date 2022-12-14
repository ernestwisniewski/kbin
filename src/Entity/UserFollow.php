<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Cache;
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
#[UniqueConstraint(name: 'user_follows_idx', columns: ['follower_id', 'following_id'])]
#[Cache('NONSTRICT_READ_WRITE')]
class UserFollow
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'follows')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $follower;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'followers')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $following;

    public function __construct(User $follower, User $following)
    {
        $this->createdAtTraitConstruct();

        $this->follower = $follower;
        $this->following = $following;
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
