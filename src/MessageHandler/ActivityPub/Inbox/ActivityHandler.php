<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\CreateMessage;
use App\Message\ActivityPub\Inbox\DeleteMessage;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Message\ActivityPub\Inbox\LikeMessage;
use App\Service\ActivityPub\SignatureValidator;
use App\Service\ActivityPubManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivityHandler implements MessageHandlerInterface
{
    public function __construct(
        private SignatureValidator $signatureValidator,
        private MessageBusInterface $bus,
        private ActivityPubManager $manager,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ActivityMessage $message): void
    {
        $payload = @json_decode($message->payload, true);

        if ($message->headers) {
//            $this->signatureValidator->validate($message->payload, $message->headers);
        }

        try {
            $user = $this->manager->findActorOrCreate($payload['actor'] ?? $payload['attributedTo']);
        } catch (\Exception $e) {
            $this->logger->error('User not found: '.$payload['actor'] ?? $payload['attributedTo']);

            return;
        }

        if ($user->isBanned) {
            return;
        }

        $this->handle($payload);
    }

    private function handle(array $payload)
    {
        switch ($payload['type']) {
            case 'Create':
                $this->bus->dispatch(new CreateMessage($payload['object']));
                break;
            case 'Note':
            case 'Page':
                $this->bus->dispatch(new CreateMessage($payload));
                break;
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
        }
    }

    private function handleUndo(array $payload): void
    {
        if (is_array($payload['object'])) {
            $type = $payload['object']['type'];
        } else {
            $type = $payload['type'];
        }

        if ($type === 'Follow') {
            $this->bus->dispatch(new FollowMessage($payload));

            return;
        }

        if ($type === 'Announce') {
            $this->bus->dispatch(new AnnounceMessage($payload));

            return;
        }

        if ($type === 'Like') {
            $this->bus->dispatch(new LikeMessage($payload));
            return;
        }
    }

    private function handleAcceptAndReject(array $payload):void
    {
        if (is_array($payload['object'])) {
            $type = $payload['object']['type'];
        } else {
            $type = $payload['type'];
        }

        if ($type === 'Follow') {
            $this->bus->dispatch(new FollowMessage($payload));
        }
    }
}
