<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Vote\EventSubscriber;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Vote\EventSubscriber\Event\VoteEvent;
use App\Message\ActivityPub\Outbox\AnnounceMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class VoteActivityPubSubscriber
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[AsEventListener(event: VoteEvent::class)]
    public function onVote(VoteEvent $event): void
    {
        if (!$event->vote->user->apId && VotableInterface::VOTE_UP === $event->vote->choice && !$event->votedAgain) {
            $this->messageBus->dispatch(
                new AnnounceMessage(
                    $event->vote->user->getId(),
                    $event->votable->getId(),
                    \get_class($event->votable),
                ),
            );
        }
    }
}
