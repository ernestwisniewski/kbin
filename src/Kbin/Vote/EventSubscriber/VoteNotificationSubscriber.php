<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Vote\EventSubscriber;

use App\Kbin\Vote\EventSubscriber\Event\VoteEvent;
use App\Message\Notification\VoteNotificationMessage;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class VoteNotificationSubscriber
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[AsEventListener(event: VoteEvent::class)]
    public function onVote(VoteEvent $event): void
    {
        $this->messageBus->dispatch(
            new VoteNotificationMessage(
                $event->votable->getId(),
                ClassUtils::getRealClass(\get_class($event->votable))
            )
        );
    }
}
