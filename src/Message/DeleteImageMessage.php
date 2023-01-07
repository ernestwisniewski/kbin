<?php

declare(strict_types=1);

namespace App\Message;

use App\Message\Contracts\AsyncMessageInterface;

class DeleteImageMessage implements AsyncMessageInterface
{
    public function __construct(public string $path, public bool $force = false)
    {
    }
}
