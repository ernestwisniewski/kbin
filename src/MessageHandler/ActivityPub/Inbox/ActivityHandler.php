<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\User;
use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\CreateMessage;
use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Message\ActivityPub\Inbox\LikeMessage;
use App\Message\ActivityPub\Inbox\UpdateMessage;
use App\Service\ActivityPub\SignatureValidator;
use App\Service\ActivityPubManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class ActivityHandler
{
    public function __construct(
        private readonly SignatureValidator $signatureValidator,
        private readonly MessageBusInterface $bus,
        private readonly ActivityPubManager $manager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(ActivityMessage $message): void
    {
        $this->logger->error('payload: '.$message->payload);
        $payload = @json_decode($message->payload, true);

        if ($message->headers) {
//            $this->signatureValidator->validate($message->payload, $message->headers);
        }

        if (isset($payload['payload'])) {
            $payload = $payload['payload'];
        }

        try {
            if (isset($payload['actor']) || isset($payload['attributedTo'])) {
                $user = $this->manager->findActorOrCreate($payload['actor'] ?? $payload['attributedTo']);
            } else {
                $user = $this->manager->findActorOrCreate($payload['id']);
            }
        } catch (\Exception $e) {
            $this->logger->error('payload: '.json_encode($payload));

            return;
        }

        if ($user instanceof User && $user->isBanned) {
            return;
        }

        $this->handle($payload);
    }

    private function handle(array $payload)
    {
        if ('Announce' === $payload['type']) {
            if (is_array($payload['object'])) {
                $payload = $payload['object'];
            }
        }

        switch ($payload['type']) {
            case 'Create':
                $this->bus->dispatch(new CreateMessage($payload['object']));
                break;
            case 'Note':
            case 'Page':
            case 'Article':
            case 'Question':
                $this->bus->dispatch(new CreateMessage($payload));
            // no break
            case 'Announce':
                $this->bus->dispatch(new AnnounceMessage($payload));
                break;
            case 'Like':
                $this->bus->dispatch(new LikeMessage($payload));
                break;
            case 'Follow':
                $this->bus->dispatch(new FollowMessage($payload));
                break;
            case 'Delete':
                $this->bus->dispatch(new DeleteMessage($payload));
                break;
            case 'Undo':
                $this->handleUndo($payload);
                break;
            case 'Accept':
            case 'Reject':
                $this->handleAcceptAndReject($payload);
                break;
            case 'Update':
                $this->bus->dispatch(new UpdateMessage($payload));
                break;
        }
    }

    private function handleUndo(array $payload): void
    {
        if (is_array($payload['object'])) {
            $type = $payload['object']['type'];
        } else {
            $type = $payload['type'];
        }

        if ('Follow' === $type) {
            $this->bus->dispatch(new FollowMessage($payload));

            return;
        }

        if ('Announce' === $type) {
            $this->bus->dispatch(new AnnounceMessage($payload));

            return;
        }

        if ('Like' === $type) {
            $this->bus->dispatch(new LikeMessage($payload));

            return;
        }
    }

    private function handleAcceptAndReject(array $payload): void
    {
        if (is_array($payload['object'])) {
            $type = $payload['object']['type'];
        } else {
            $type = $payload['type'];
        }

        if ('Follow' === $type) {
            $this->bus->dispatch(new FollowMessage($payload));
        }
    }
}
