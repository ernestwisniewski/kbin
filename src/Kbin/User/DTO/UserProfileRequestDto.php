<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class UserProfileRequestDto
{
    public ?string $about = null;
}
