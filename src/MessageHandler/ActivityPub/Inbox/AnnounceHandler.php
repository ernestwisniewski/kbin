<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\ActivityPub\Inbox;

use App\Entity\User;
use App\Kbin\Vote\EventSubscriber\VoteCacheSubscriber;
use App\Kbin\Vote\VoteUp;
use App\Message\ActivityPub\Inbox\AnnounceMessage;
use App\Message\ActivityPub\Inbox\ChainActivityMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPubManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class AnnounceHandler
{
    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApActivityRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly VoteUp $voteUp,
        private readonly VoteCacheSubscriber $voteCacheSubscriber,
        private readonly ApHttpClient $apHttpClient,
    ) {
    }

    public function __invoke(AnnounceMessage $message): void
    {
        if ('Announce' === $message->payload['type']) {
            $activity = $this->repository->findByObjectId($message->payload['object']);

            if ($activity) {
                $entity = $this->entityManager->getRepository($activity['type'])->find((int) $activity['id']);
            } else {
                $object = $this->apHttpClient->getActivityObject($message->payload['object']);

                $this->bus->dispatch(new ChainActivityMessage([$object], null, $message->payload));

                return;
            }

            $actor = $this->activityPubManager->findActorOrCreate($message->payload['actor']);

            if ($actor instanceof User) {
                ($this->voteUp)($entity, $actor);
                $this->voteCacheSubscriber->onVote($entity);
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
