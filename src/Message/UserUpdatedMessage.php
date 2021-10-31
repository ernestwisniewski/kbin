<?php declare(strict_types = 1);

namespace App\Message;

use App\Message\Contracts\SendConfirmationEmailInterface;

class UserUpdatedMessage implements SendConfirmationEmailInterface
{
    public function __construct(public int $userId)
    {
    }
}
