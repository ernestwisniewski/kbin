<?php

declare(strict_types=1);

namespace App\Message;

use App\Message\Contracts\AsyncApMessageInterface;

class LinkEmbedMessage implements AsyncApMessageInterface
{
    public function __construct(public string $body)
    {
    }
}
