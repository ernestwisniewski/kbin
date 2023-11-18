<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use App\Message\ActivityPub\Inbox\ActivityMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class MagazineInboxController
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

        $this->logger->info('MagazineInboxController:request: '.$requestInfo['method'].' '.$requestInfo['uri']);
        $this->logger->info('MagazineInboxController:headers: '.$request->headers);
        $this->logger->info('MagazineInboxController:content: '.$request->getContent());

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
