<?php declare(strict_types = 1);

namespace App\Message\Notification;

class PostCreatedNotificationMessage
{
    public function __construct(public int $postId)
    {
    }
}
