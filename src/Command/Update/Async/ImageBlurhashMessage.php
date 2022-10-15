<?php

namespace App\Command\Update\Async;

use App\Message\AsyncMessageInterface;

class ImageBlurhashMessage implements AsyncMessageInterface
{
    public function __construct(public int $id)
    {
    }
}