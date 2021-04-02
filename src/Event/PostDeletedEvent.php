<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Post;
use App\Entity\User;

class PostDeletedEvent
{
    public function __construct(private Post $post, private ?User $user = null)
    {
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
