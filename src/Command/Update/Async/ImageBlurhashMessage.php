<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Command\Update\Async;

use App\Message\Contracts\AsyncMessageInterface;

class ImageBlurhashMessage implements AsyncMessageInterface
{
    public function __construct(public int $id)
    {
    }
}
