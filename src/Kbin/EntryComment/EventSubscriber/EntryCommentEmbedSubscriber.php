<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentEditedEvent;
use App\Kbin\MessageBus\LinkEmbedMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EntryCommentEmbedSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: EntryCommentCreatedEvent::class)]
    #[AsEventListener(event: EntryCommentEditedEvent::class)]
    public function attachEmbed(EntryCommentCreatedEvent|EntryCommentEditedEvent $event): void
    {
        if ($event->comment->body) {
            $this->messageBus->dispatch(new LinkEmbedMessage($event->comment->body));
        }
    }
}
