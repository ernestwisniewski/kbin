<?php declare(strict_types = 1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryCreatedEvent;
use App\Message\EntryEmbedMessage;
use App\Message\Notification\EntryCreatedNotificationMessage;
use App\Service\DomainManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus, private DomainManager $manager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCreatedEvent::class => 'onEntryCreated',
        ];
    }

    public function onEntryCreated(EntryCreatedEvent $event): void
    {
        $this->manager->extract($event->entry);
        $this->bus->dispatch(new EntryEmbedMessage($event->entry->getId()));
        $this->bus->dispatch(new EntryCreatedNotificationMessage($event->entry->getId()));
    }
}
