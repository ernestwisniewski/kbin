<?php

declare(strict_types=1);

namespace App\EventSubscriber\EntryComment;

use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\Notification\EntryCommentDeletedNotificationMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;

class EntryCommentDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly MessageBusInterface $bus,
        private readonly DeleteWrapper $deleteWrapper
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentDeletedEvent::class => 'onEntryCommentDeleted',
            EntryCommentBeforePurgeEvent::class => 'onEntryCommentBeforePurge',
        ];
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()]);

        $this->bus->dispatch(new EntryCommentDeletedNotificationMessage($event->comment->getId()));
    }

    public function onEntryCommentBeforePurge(EntryCommentBeforePurgeEvent $event): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()]);

        $this->bus->dispatch(new EntryCommentDeletedNotificationMessage($event->comment->getId()));

        if (!$event->comment->apId) {
            new DeleteMessage(
                $this->deleteWrapper->build($event->comment, Uuid::v4()->toRfc4122()),
                $event->comment->user->getId(),
                $event->comment->magazine->getId()
            );
        }
    }
}
