<?php declare(strict_types = 1);

namespace App\Message;

use App\Message\Contracts\SendConfirmationEmailInterface;

class UserUpdatedMessage implements SendConfirmationEmailInterface
{
    public function __construct(private int $userId)
    {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
