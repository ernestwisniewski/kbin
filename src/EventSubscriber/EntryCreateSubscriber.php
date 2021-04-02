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
    public function __construct(private MessageBusInterface $messageBus, private DomainManager $domainManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCreatedEvent::class => 'onEntryCreated',
        ];
    }

    public function onEntryCreated(EntryCreatedEvent $event)
    {
        $this->domainManager->extract($event->getEntry());
        $this->messageBus->dispatch(new EntryEmbedMessage($event->getEntry()->getId()));
        $this->messageBus->dispatch(new EntryNotificationMessage($event->getEntry()->getId()));
    }
}
