<?php declare(strict_types = 1);

namespace App\Message;

class PostNotificationMessage
{
    public function __construct(private int $postId)
    {
    }

    public function getPostId(): int
    {
        return $this->postId;
    }
}
