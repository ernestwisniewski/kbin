<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\ActivityPub\Type\Extended\Object;

use ActivityPhp\Type\Core\ObjectType;

class PropertyValue extends ObjectType
{
    protected $type = 'PropertyValue';
    protected ?string $value = null;
}
