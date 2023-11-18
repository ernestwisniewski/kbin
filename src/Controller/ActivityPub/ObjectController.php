<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ObjectController
{
    public function __invoke(string|int $id, Request $request): JsonResponse
    {
        $response = new JsonResponse();

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
