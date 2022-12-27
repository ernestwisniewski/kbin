<?php declare(strict_types=1);

namespace App\Controller\ActivityPub\Magazine;

use App\Message\ActivityPub\Inbox\ActivityMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class MagazineInboxController
{
    public function __construct(private MessageBusInterface $bus, private LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->logger->info('UserInboxController:headers: '.$request->headers);
        $this->logger->info('UserInboxController:content: '.$request->getContent());

        $this->bus->dispatch(new ActivityMessage($request->getContent(), $request->headers->all()));

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
