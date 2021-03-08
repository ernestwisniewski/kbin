<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Post;

class PostBeforePurgeEvent
{
    protected Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}
