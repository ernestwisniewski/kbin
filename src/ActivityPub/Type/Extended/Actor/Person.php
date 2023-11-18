<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\ActivityPub\Type\Extended\Actor;

use ActivityPhp\Type\Extended\Actor\Person as BasePerson;

class Person extends BasePerson
{
    protected $inbox;
}
