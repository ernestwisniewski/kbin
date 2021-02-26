<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\PostComment;
use App\Entity\Post;

class PostCommentDto
{
    private ?int $id = null;
    private Post $post;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 3,
     *     max = 4500
     * )
     */
    private ?string $body;
    private ?PostComment $parent = null;

    public function create(Post $post, string $body, ?int $id = null): self
    {
        $this->id   = $id;
        $this->post = $post;
        $this->body = $body;

        return $this;
    }

    public function createWithParent(Post $post, ?PostComment $parent, ?string $body = null): self
    {
        $this->post   = $post;
        $this->parent = $parent;
        $this->body   = $body;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getParent(): ?PostComment
    {
        return $this->parent;
    }

    public function setParent(?PostComment $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
