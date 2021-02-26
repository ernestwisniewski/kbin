<?php declare(strict_types=1);

namespace App\Message;

class PostCreatedMessage
{
    private int $postId;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }
}
