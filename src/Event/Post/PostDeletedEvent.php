<?php declare(strict_types = 1);

namespace App\Event\Post;

use App\Entity\Post;
use App\Entity\User;

class PostDeletedEvent
{
    public function __construct(public Post $post, public ?User $user = null)
    {
    }
}
