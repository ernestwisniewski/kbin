<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: FavouriteRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'favourite_type', type: 'text')]
#[DiscriminatorMap([
    'entry' => 'EntryFavourite',
    'entry_comment' => 'EntryCommentFavourite',
    'post' => 'PostFavourite',
    'post_comment' => 'PostCommentFavourite',
])]
abstract class Favourite
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: Magazine::class)]
    #[JoinColumn(nullable: false)]
    public Magazine $magazine;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'favourites')]
    #[JoinColumn(nullable: false)]
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    abstract public function getType(): string;

    abstract public function getSubject(): FavouriteInterface;

    abstract public function clearSubject(): Favourite;
}
