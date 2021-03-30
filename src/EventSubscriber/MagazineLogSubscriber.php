<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\MagazineLogEntryDelete;
use App\Event\EntryDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MagazineLogSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class => 'onEntryDeleted',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        if (!$event->getEntry()->isTrashed()) {
            return;
        }

        if (!$event->getUser() || $event->getEntry()->isAuthor($event->getUser())) {
            return;
        }

        $log = new MagazineLogEntryDelete($event->getEntry(), $event->getUser());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
