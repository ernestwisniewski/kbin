<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class PostCommentDto
{
    public Magazine|MagazineDto|null $magazine;
    public User|UserDto|null $user = null;
    public Post|PostDto $post;
    #[Assert\Length(min: 2, max: 5000)]
    public ?PostComment $parent = null;
    public ?Image $image = null;
    public ?string $body;
    public ?int $uv = null;
    public ?string $ip = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTime $lastActive = null;
    private ?int $id = null;

    public function create(
        Post $post,
        User $user,
        string $body,
        ?Image $image = null,
        ?int $uv = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTime $lastActive = null,
        ?int $id = null
    ): self {
        $this->id         = $id;
        $this->user       = $user;
        $this->magazine   = $post->magazine;
        $this->user       = $post->user;
        $this->post       = $post;
        $this->image      = $image;
        $this->body       = $body;
        $this->uv         = $uv;
        $this->createdAt  = $createdAt;
        $this->lastActive = $lastActive;

        return $this;
    }

    public function createWithParent(Post $post, ?PostComment $parent, ?Image $image = null, ?string $body = null): self
    {
        $this->post   = $post;
        $this->parent = $parent;
        $this->body   = $body;
        $this->image  = $image;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
