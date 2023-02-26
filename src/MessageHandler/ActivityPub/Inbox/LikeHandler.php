<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Message\ActivityPub\Inbox\LikeMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\FavouriteManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class LikeHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApActivityRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly FavouriteManager $manager,
        private readonly ApHttpClient $apHttpClient,
    ) {
    }

    public function __invoke(LikeMessage $message): void
    {
        if ('Like' === $message->payload['type']) {
            $activity = $this->repository->findByObjectId($message->payload['object']);

            if ($activity) {
                $entity = $this->entityManager->getRepository($activity['type'])->find((int) $activity['id']);
            } else {
                $object = $this->apHttpClient->getActivityObject($message->payload['object']);

                $this->bus->dispatch(new ChainActivityMessage([$object], null, null, $message->payload));

                return;
            }

            $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

            $this->manager->toggle($actor, $entity, FavouriteManager::TYPE_LIKE);
        }

        if ('Undo' === $message->payload['type']) {
            if ('Like' === $message->payload['object']['type']) {
                $activity = $this->repository->findByObjectId($message->payload['object']['object']);
                $entity = $this->entityManager->getRepository($activity['type'])->find((int) $activity['id']);
                $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

                $this->manager->toggle($actor, $entity, FavouriteManager::TYPE_UNLIKE);
            }
        }

        if (null === $entity->magazine->apId) {
            $this->bus->dispatch(
                new \App\Message\ActivityPub\Outbox\LikeMessage(
                    $actor->getId(),
                    $entity->getId(),
                    get_class($entity)
                )
            );
        }
    }
}
