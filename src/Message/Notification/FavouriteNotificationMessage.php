<?php

declare(strict_types=1);

namespace App\Message\Notification;

use App\Message\Contracts\AsyncMessageInterface;

class FavouriteNotificationMessage implements AsyncMessageInterface
{
    public function __construct(public int $subjectId, public string $subjectClass)
    {
    }
}
