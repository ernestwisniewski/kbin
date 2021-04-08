<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\EntryCommentNotificationMessage;
use App\Event\EntryCommentCreatedEvent;

class EntryCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $messageBus)
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
        $this->messageBus->dispatch(new EntryCommentNotificationMessage($event->comment->getId()));
    }
}
