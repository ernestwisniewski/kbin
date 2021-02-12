<?php declare(strict_types = 1);

namespace App\Message;

use App\Message\Contracts\SendConfirmationEmailInterface;

class UserCreatedMessage implements SendConfirmationEmailInterface
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
