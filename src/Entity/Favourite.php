<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\FavouriteRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="favourite_type", type="text")
 * @ORM\DiscriminatorMap({
 *     "entry": "EntryFavourite",
 *     "entry_comment": "EntryCommentFavourite",
 *     "post": "PostFavourite",
 *     "post_comment": "PostCommentFavourite",
 * })
 */
abstract class Favourite
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Magazine")
     */
    public Magazine $magazine;
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="favourites")
     */
    public User $user;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

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
