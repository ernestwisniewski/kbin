<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryCreatedEvent;
use App\Message\EntryEmbedMessage;
use App\Message\EntryNotificationMessage;
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
        $this->bus->dispatch(new EntryNotificationMessage($event->entry->getId()));
    }
}
