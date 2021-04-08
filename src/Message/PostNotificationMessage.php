<?php declare(strict_types=1);

namespace App\Message;

class PostNotificationMessage
{
    public function __construct(public int $postId)
    {
    }
}
