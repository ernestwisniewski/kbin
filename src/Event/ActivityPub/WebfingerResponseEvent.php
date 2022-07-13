<?php declare(strict_types=1);

namespace App\Event\ActivityPub;

use App\ActivityPub\JsonRd;

class WebfingerResponseEvent
{
    public function __construct(public JsonRd $jsonRd)
    {
    }
}
