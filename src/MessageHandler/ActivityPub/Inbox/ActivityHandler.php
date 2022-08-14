<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\ActivityMessage;
use App\Message\ActivityPub\Inbox\CreateMessage;
use App\Message\ActivityPub\Inbox\FollowMessage;
use App\Service\ActivityPub\SignatureValidator;
use App\Service\ActivityPubManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivityHandler implements MessageHandlerInterface
{
    public function __construct(private SignatureValidator $signatureValidator, private MessageBusInterface $bus, private ActivityPubManager $manager)
    {
    }

    public function __invoke(ActivityMessage $message): void
    {
        $payload = @json_decode($message->payload, true);

        if ($message->headers) {
            $this->signatureValidator->validate($message->payload, $message->headers);
        }

        $user = $this->manager->findActorOrCreate($payload['actor']);
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
                $this->handleAnnounce($payload);
                break;
            case 'Follow':
            case 'Undo':
                $this->bus->dispatch(new FollowMessage($payload));
                break;
        }
    }

    private function handleAnnounce(array $payload)
    {
    }
}
