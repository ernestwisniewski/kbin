<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Repository\DomainRepository;
use App\Event\EntryCreatedEvent;
use App\Service\DomainManager;

class DomainSubscriber implements EventSubscriberInterface
{
    private DomainRepository $domainRepository;
    private DomainManager $domainManager;

    public function __construct(DomainRepository $domainRepository, DomainManager $domainManager)
    {

        $this->domainRepository = $domainRepository;
        $this->domainManager    = $domainManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCreatedEvent::class => 'onEntryCreated',
        ];
    }

    public function onEntryCreated(EntryCreatedEvent $event): void
    {
        $this->domainManager->extract($event->getEntry());
    }
}
