<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Entry;
use App\Event\EntryCreatedEvent;
use App\Event\EntryUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class EntryCountsSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCreatedEvent::class => 'onEntryCreated',
            EntryUpdatedEvent::class => 'onEntryUpdated',
        ];
    }

    public function onEntryCreated(EntryCreatedEvent $event): void
    {
        $this->updateCounts($event->getEntry());
    }

    public function onEntryUpdated(EntryUpdatedEvent $event): void
    {
        $this->updateCounts($event->getEntry());
    }

    public function updateCounts(Entry $entry):void
    {
    }
}
