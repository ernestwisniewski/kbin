<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Outbox;

use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeliverMessage;
use App\Repository\UserRepository;
use App\Service\ActivityPub\Wrapper\CreateWrapper;
use App\Service\ActivityPubManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateHandler implements MessageHandlerInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private UserRepository $repository,
        private CreateWrapper $createWrapper,
        private EntityManagerInterface $entityManager,
        private ActivityPubManager $activityPubManager
    ) {
    }

    public function __invoke(CreateMessage $message): void
    {
        $entity = $this->entityManager->getRepository($message->type)->find($message->id);

        $activity = $this->createWrapper->build($entity);

        $followers = $this->repository->findAudience($entity->user);

        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower->apProfileId, $activity));
        }

        $followers = $this->activityPubManager->getFollowersFromObject($activity, $entity->user);
        foreach ($followers as $follower) {
            $this->bus->dispatch(new DeliverMessage($follower, $activity));
        }
    }
}
