<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\MagazineLogEntryCommentDelete;
use App\Entity\MagazineLogPostCommentDelete;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentDeletedEvent;
use App\Event\PostCommentDeletedEvent;
use App\Entity\MagazineLogEntryDelete;
use App\Entity\MagazineLogPostDelete;
use App\Event\EntryDeletedEvent;
use App\Event\MagazineBanEvent;
use App\Event\PostDeletedEvent;
use App\Entity\MagazineLogBan;

class MagazineLogSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class        => 'onEntryDeleted',
            EntryCommentDeletedEvent::class => 'onEntryCommentDeleted',
            PostDeletedEvent::class         => 'onPostDeleted',
            PostCommentDeletedEvent::class  => 'onPostCommentDeleted',
            MagazineBanEvent::class         => 'onBan',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        if (!$event->entry->isTrashed()) {
            return;
        }

        if (!$event->user || $event->entry->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryDelete($event->entry, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        if (!$event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryCommentDelete($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onPostDeleted(PostDeletedEvent $event): void
    {
        if (!$event->post->isTrashed()) {
            return;
        }

        if (!$event->user || $event->post->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostDelete($event->post, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        if (!$event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostCommentDelete($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onBan(MagazineBanEvent $event): void
    {
        $log = new MagazineLogBan($event->ban);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
