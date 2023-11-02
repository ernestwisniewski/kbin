<?php

declare(strict_types=1);

namespace App\Message;

class MagazinePurgeMessage
{
    public function __construct(public int $id, public bool $contentOnly)
    {
    }
}
