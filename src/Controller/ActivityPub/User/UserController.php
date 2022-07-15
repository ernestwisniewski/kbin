<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use App\Entity\User;
use App\Factory\ActivityPub\PersonFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController
{
    public function __construct(private PersonFactory $personFactory)
    {
    }

    public function __invoke(User $user): JsonResponse
    {
        $response = new JsonResponse($this->personFactory->create($user));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
