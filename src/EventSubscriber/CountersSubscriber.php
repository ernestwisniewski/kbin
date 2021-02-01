<?php declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Entry;
use App\Event\EntryCommentPurgedEvent;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Event\EntryCreatedEvent;
use App\Event\EntryUpdatedEvent;
use App\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class CountersSubscriber implements EventSubscriberInterface
{
    private EntryRepository $entryRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntryRepository $entryRepository, EntityManagerInterface $entityManager)
    {
        $this->entryRepository = $entryRepository;
        $this->entityManager   = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentCreatedEvent::class => 'onCommentCreated',
            EntryCommentUpdatedEvent::class => 'onCommentUpdated',
            EntryCommentPurgedEvent::class  => 'onCommentBeforePurged',
        ];
    }

    public function onCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $magazine = $event->getComment()->getEntry()->getMagazine();
        $magazine->setCommentCount(
            $this->entryRepository->countCommentsByMagazine($magazine)
        );

        $this->entityManager->flush();
    }

    public function onCommentUpdated(EntryCommentUpdatedEvent $event): void
    {
    }

    public function onCommentBeforePurged(EntryCommentPurgedEvent $event): void
    {
        $event->getMagazine()->setCommentCount(
            $this->entryRepository->countCommentsByMagazine($event->getMagazine())
        );

        $this->entityManager->flush();
    }
}
