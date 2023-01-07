<?php

declare(strict_types=1);

namespace App\ActivityPub\Type\Extended\Object;

use ActivityPhp\Type\Core\ObjectType;

class PropertyValue extends ObjectType
{
    protected $type = 'PropertyValue';
    protected ?string $value = null;
}
