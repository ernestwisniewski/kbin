<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class Vote
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'integer', nullable: false)]
    public int $choice;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public User $user;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public User $author;

    public function __construct(int $choice, User $user, User $author)
    {
        $this->choice = $choice;
        $this->user = $user;
        $this->author = $author;

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
