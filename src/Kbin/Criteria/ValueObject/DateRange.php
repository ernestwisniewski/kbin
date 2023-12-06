<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Criteria\ValueObject;

use Webmozart\Assert\Assert;

class DateRange
{
    public function __construct(public \DateTimeImmutable $from, public \DateTimeImmutable $to)
    {
        Assert::lessThanEq($from, $to);
    }
}
