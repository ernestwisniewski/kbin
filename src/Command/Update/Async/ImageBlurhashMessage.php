<?php

declare(strict_types=1);

namespace App\Command\Update\Async;

use App\Message\Contracts\AsyncMessageInterface;

class ImageBlurhashMessage implements AsyncMessageInterface
{
    public function __construct(public int $id)
    {
    }
}
