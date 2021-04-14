<?php declare(strict_types=1);

namespace App\Message;

class BanNotificationMessage
{
    public function __construct(public int $banId)
    {
    }
}
