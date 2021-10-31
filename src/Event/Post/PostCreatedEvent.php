<?php declare(strict_types = 1);

namespace App\Event\Post;

use App\Entity\Post;

class PostCreatedEvent
{
    public function __construct(public Post $post)
    {
    }
}
