<?php

declare(strict_types=1);

namespace App\ActivityPub\Type\Extended\Object;

use ActivityPhp\Type\Core\ObjectType;

class Emoji extends ObjectType
{
    protected $type = 'Emoji';
    protected ?string $value = null;
}
