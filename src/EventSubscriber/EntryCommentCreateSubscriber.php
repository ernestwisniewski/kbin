<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryCommentCreatedEvent;
use App\Message\EntryCommentNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentCreatedEvent::class => 'onEntryCommentCreated',
        ];
    }

    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $this->bus->dispatch(new EntryCommentNotificationMessage($event->comment->getId()));
    }
}
