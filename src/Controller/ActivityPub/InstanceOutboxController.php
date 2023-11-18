<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Factory\ActivityPub\InstanceFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class InstanceOutboxController
{
    public function __invoke(string $kbinDomain, Request $request, InstanceFactory $instanceFactory): JsonResponse
    {
        return new JsonResponse([]);
    }
}
