<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\MagazineSubscribedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Event\EntryCommentPurgedEvent;
use App\Repository\EntryRepository;

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
            MagazineSubscribedEvent::class  => 'onMagazineSubscription',
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

    public function onMagazineSubscription(MagazineSubscribedEvent $event): void
    {
        $event->getMagazine()->setSubscriptionsCount(
            $event->getMagazine()->getSubscriptions()->count()
        );

        $this->entityManager->flush();
    }
}
