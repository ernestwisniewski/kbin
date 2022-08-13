<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Factory\ActivityPub\ActivityFactory;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateHandler implements MessageHandlerInterface
{
    public function __construct(private ActivityFactory $factory, private MessageBusInterface $bus, private UserRepository $repository)
    {
    }

    public function __invoke(CreateMessage $message): void
    {
        $activity = $this->factory->create($message->activity, true);

        $followers = $this->repository->findAudience($message->activity->user);

        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }
    }
}
