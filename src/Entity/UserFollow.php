<?php

namespace App\Entity;

use App\Repository\UserFollowRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Collection;

/**
 * @ORM\Entity(repositoryClass=UserFollowRepository::class)
 */
class UserFollow
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="follows")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $follower;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="following")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $following;

    public function __construct(User $follower, User $following)
    {
        $this->follower  = $follower;
        $this->following = $following;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): self
    {
        $this->follower = $follower;

        return $this;
    }

    public function getFollowing(): ?User
    {
        return $this->following;
    }

    public function setFollowing(?User $following): self
    {
        $this->following = $following;

        return $this;
    }
}
