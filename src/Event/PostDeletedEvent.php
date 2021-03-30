<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Post;
use App\Entity\User;

class PostDeletedEvent
{
    protected Post $post;
    protected ?User $user;

    public function __construct(Post $post, ?User $user = null)
    {
        $this->post = $post;
        $this->user = $user;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
