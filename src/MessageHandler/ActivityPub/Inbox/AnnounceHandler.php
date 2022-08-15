<?php declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPubManager;
use App\Service\VoteManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AnnounceHandler implements MessageHandlerInterface
{
    public function __construct(
        private ActivityPubManager $activityPubManager,
        private ApActivityRepository $repository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private VoteManager $manager
    ) {
    }

    public function __invoke(AnnounceMessage $message): void
    {
        if ($message->payload['type'] === 'Announce') {
            $activity = $this->repository->findByObjectId($message->payload['object']);

            if ($activity) {
                $entity = $this->entityManager->getRepository($activity['type'])->find((int) $activity['id']);
            } else {
                $this->bus->dispatch(new ChainActivityMessage([$message->payload['object']], null, $message->payload));

                return;
            }

            $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

            $this->manager->upvote($entity, $actor);
        }

        if ($message->payload['type'] === 'Undo') {
            return;
        }
    }
}
