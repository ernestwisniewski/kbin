<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class PostCommentDto
{
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Post|PostDto|null $post = null;
    public ?PostComment $parent = null;
    public ?Image $image = null;
    #[Assert\Length(min: 2, max: 5000)]
    public ?string $body = null;
    public int $uv = 0;
    public ?string $ip = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTime $lastActive = null;
    private ?int $id = null;

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

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
