<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Factory\ActivityPub\PersonFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function __construct(private readonly PersonFactory $personFactory)
    {
    }

    public function __invoke(User $user, Request $request): JsonResponse
    {
        if ($user->apId) {
            throw $this->createNotFoundException();
        }

        $response = new JsonResponse($this->personFactory->create($user, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
