<?php declare(strict_types = 1);

namespace App\Message\Notification;

class VoteNotificationMessage
{
    public function __construct(public int $subjectId, public string $subjectClass)
    {
    }
}
