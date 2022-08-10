<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Service\MentionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class SharedInboxController
{
    public function __construct(
        private MessageBusInterface $bus,
        private MentionManager $mentionManager,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->logger->error($request->getContent(), (string) $request->headers);

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
