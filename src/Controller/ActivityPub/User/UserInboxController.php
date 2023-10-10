<?php

declare(strict_types=1);

namespace App\Controller\ActivityPub\User;

use App\Message\ActivityPub\Inbox\ActivityMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class UserInboxController
{
    public function __construct(private readonly MessageBusInterface $bus, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $requestInfo = array_filter(
            [
                'host' => $request->getHost(),
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'client_ip' => $request->getClientIp(),
            ]
        );

        $this->logger->info('UserInboxController:request: '.$requestInfo['method'].' '.$requestInfo['uri']);
        $this->logger->info('UserInboxController:headers: '.$request->headers);
        $this->logger->info('UserInboxController:content: '.$request->getContent());

        $this->bus->dispatch(
            new ActivityMessage(
                $request->getContent(),
                $requestInfo,
                $request->headers->all(),
            )
        );

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
