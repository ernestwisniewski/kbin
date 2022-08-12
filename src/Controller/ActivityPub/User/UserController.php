<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use App\Entity\User;
use App\Factory\ActivityPub\PersonFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController
{
    public function __construct(private PersonFactory $personFactory)
    {
    }

    public function __invoke(User $user, Request $request): JsonResponse
    {
        $response = new JsonResponse($this->personFactory->create($user, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
