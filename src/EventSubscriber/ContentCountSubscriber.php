<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryCommentDeletedEvent;
use App\Event\EntryDeletedEvent;
use App\Event\MagazineSubscribedEvent;
use App\Event\PostCommentCreatedEvent;
use App\Event\PostCommentDeletedEvent;
use App\Event\PostCommentPurgedEvent;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Event\EntryCommentPurgedEvent;
use App\Repository\EntryRepository;

class ContentCountSubscriber implements EventSubscriberInterface
{
    private EntryRepository $entryRepository;
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntryRepository $entryRepository, PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $this->entryRepository = $entryRepository;
        $this->postRepository = $postRepository;
        $this->entityManager   = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class => 'onEntryDeleted',
            EntryCommentCreatedEvent::class => 'onEntryCommentCreated',
            EntryCommentDeletedEvent::class  => 'onEntryCommentDeleted',
            EntryCommentPurgedEvent::class  => 'onEntryCommentPurged',
            PostCommentCreatedEvent::class => 'onPostCommentCreated',
            PostCommentDeletedEvent::class  => 'onPostCommentDeleted',
            PostCommentPurgedEvent::class  => 'onPostCommentPurged',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event)
    {
        $event->getEntry()->getMagazine()->updateEntryCounts();

        $this->entityManager->flush();
    }

    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $magazine = $event->getComment()->getEntry()->getMagazine();
        $magazine->setEntryCommentCount(
            $this->entryRepository->countEntryCommentsByMagazine($magazine)
        );

        $this->entityManager->flush();
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $magazine = $event->getComment()->getEntry()->getMagazine();
        $magazine->setEntryCommentCount(
            $this->entryRepository->countEntryCommentsByMagazine($magazine) - 1
        );

        $event->getComment()->getEntry()->updateCounts();

        $this->entityManager->flush();
    }

    public function onEntryCommentPurged(EntryCommentPurgedEvent $event): void
    {
        $event->getMagazine()->setEntryCommentCount(
            $this->entryRepository->countEntryCommentsByMagazine($event->getMagazine())
        );

        $this->entityManager->flush();
    }

    public function onPostCommentCreated(PostCommentCreatedEvent $event): void
    {
        $magazine = $event->getComment()->getPost()->getMagazine();
        $magazine->setPostCommentCount(
            $this->postRepository->countPostCommentsByMagazine($magazine)
        );

        $this->entityManager->flush();
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $magazine = $event->getComment()->getPost()->getMagazine();
        $magazine->setPostCommentCount(
            $this->postRepository->countPostCommentsByMagazine($magazine) - 1
        );

        $event->getComment()->getPost()->updateCounts();

        $this->entityManager->flush();
    }

    public function onPostCommentPurged(PostCommentPurgedEvent $event): void
    {
        $event->getMagazine()->setPostCommentCount(
            $this->postRepository->countPostCommentsByMagazine($event->getMagazine())
        );

        $this->entityManager->flush();
    }
}
