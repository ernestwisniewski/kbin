<?php

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\User;
use App\EventSubscriber\VoteHandleSubscriber;
use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use App\Service\VoteManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AnnounceHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApActivityRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly VoteManager $manager,
        private readonly VoteHandleSubscriber $voteHandleSubscriber,
        private readonly ApHttpClient $apHttpClient,
    ) {
    }

    public function __invoke(AnnounceMessage $message): void
    {
        if ('Announce' === $message->payload['type']) {
            $activity = $this->repository->findByObjectId($message->payload['object']);

            if ($activity) {
                $entity = $this->entityManager->getRepository($activity['type'])->find((int)$activity['id']);
            } else {
                $object = $this->apHttpClient->getActivityObject($message->payload['object']);

                $this->bus->dispatch(new ChainActivityMessage([$object], null, $message->payload));

                return;
            }

            $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

            if ($actor instanceof User) {
                $this->manager->upvote($entity, $actor);
                $this->voteHandleSubscriber->clearCache($entity);

//                if (null === $entity->magazine->apId) {
//                    $this->bus->dispatch(
//                        new \App\Message\ActivityPub\Outbox\AnnounceMessage(
//                            $actor->getId(),
//                            $entity->getId(),
//                            get_class($entity)
//                        )
//                    );
//                }
            } else {
                $entity->lastActive = new \DateTime();
                $this->entityManager->flush();
            }
        }

        if ('Undo' === $message->payload['type']) {
            return;
        }
    }
}
