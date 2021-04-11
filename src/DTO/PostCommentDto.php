<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Post;
use App\Entity\PostComment;
use Symfony\Component\Validator\Constraints as Assert;

class PostCommentDto
{
    public Post $post;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 4500
     * )
     */
    public ?string $body;
    public ?PostComment $parent = null;
    public ?Image $image = null;
    private ?int $id = null;

    public function create(Post $post, string $body, ?Image $image = null, ?int $id = null): self
    {
        $this->id    = $id;
        $this->post  = $post;
        $this->body  = $body;
        $this->image = $image;

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
