<?php

declare(strict_types=1);

namespace App\EventSubscriber\EntryComment;

use App\Event\EntryComment\EntryCommentBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryCommentBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentBeforeDeletedEvent::class => 'onEntryCommentBeforeDelete',
        ];
    }

    public function onEntryCommentBeforeDelete(EntryCommentBeforeDeletedEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->bus->dispatch(new DeleteMessage($event->comment->getId(), get_class($event->comment)));
        }
    }
}
