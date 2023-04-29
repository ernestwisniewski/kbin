<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Factory\ActivityPub\InstanceFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class InstanceController
{
    public function __invoke(Request $request, InstanceFactory $instanceFactory): JsonResponse
    {
        return new JsonResponse($instanceFactory->create());
    }
}
