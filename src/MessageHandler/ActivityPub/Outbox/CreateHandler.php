<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\CreateWrapper;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateHandler implements MessageHandlerInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private UserRepository $repository,
        private CreateWrapper $createWrapper
    ) {
    }

    public function __invoke(CreateMessage $message): void
    {
        $activity = $this->createWrapper->build($message->activity);

        $followers = $this->repository->findAudience($message->activity->user);

        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }
    }
}
