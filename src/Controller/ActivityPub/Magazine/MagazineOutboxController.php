<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use Symfony\Component\HttpFoundation\JsonResponse;

class MagazineOutboxController
{
    public function __construct()
    {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse();
    }
}
