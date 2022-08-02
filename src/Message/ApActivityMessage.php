<?php declare(strict_types=1);

namespace App\Message;

class ApActivityMessage
{
    public function __construct(public string $payload, public array $headers)
    {
    }
}
