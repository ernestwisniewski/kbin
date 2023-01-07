<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ObjectController
{
    public function __invoke(int $id, Request $request): JsonResponse
    {
        $response = new JsonResponse();

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
