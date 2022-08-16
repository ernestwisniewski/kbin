<?php declare(strict_types = 1);

namespace App\Message;

class DeleteImageMessage implements AsyncMessageInterface
{
    public function __construct(public string $path, public bool $force = false)
    {
    }
}
