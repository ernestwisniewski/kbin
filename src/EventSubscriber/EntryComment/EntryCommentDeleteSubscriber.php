<?php declare(strict_types=1);

namespace App\EventSubscriber\EntryComment;

use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Message\Notification\EntryCommentDeletedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryCommentDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentDeletedEvent::class     => 'onEntryCommentDeleted',
            EntryCommentBeforePurgeEvent::class => 'onEntryCommentBeforePurge',
        ];
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $this->bus->dispatch(new EntryCommentDeletedNotificationMessage($event->comment->getId()));
    }

    public function onEntryCommentBeforePurge(EntryCommentBeforePurgeEvent $event): void
    {
        $this->bus->dispatch(new EntryCommentDeletedNotificationMessage($event->comment->getId()));
    }
}
