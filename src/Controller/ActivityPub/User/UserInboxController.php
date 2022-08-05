<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class UserInboxController
{
    public function __construct(private MessageBusInterface $bus, private LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->logger->error('Headers: '.var_export($request->headers, true));

        $response = new JsonResponse([]);

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
