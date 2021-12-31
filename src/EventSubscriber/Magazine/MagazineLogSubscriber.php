<?php declare(strict_types=1);

namespace App\EventSubscriber\Magazine;

use App\Entity\MagazineLogBan;
use App\Entity\MagazineLogEntryCommentDeleted;
use App\Entity\MagazineLogEntryCommentRestored;
use App\Entity\MagazineLogEntryDeleted;
use App\Entity\MagazineLogEntryRestored;
use App\Entity\MagazineLogPostCommentDeleted;
use App\Entity\MagazineLogPostCommentRestored;
use App\Entity\MagazineLogPostDeleted;
use App\Entity\MagazineLogPostRestored;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\Entry\EntryRestoredEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\EntryComment\EntryCommentRestoredEvent;
use App\Event\Magazine\MagazineBanEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\Post\PostRestoredEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Event\PostComment\PostCommentRestoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MagazineLogSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class         => 'onEntryDeleted',
            EntryRestoredEvent::class        => 'onEntryRestored',
            EntryCommentDeletedEvent::class  => 'onEntryCommentDeleted',
            EntryCommentRestoredEvent::class => 'onEntryCommentRestored',
            PostDeletedEvent::class          => 'onPostDeleted',
            PostRestoredEvent::class         => 'onPostRestored',
            PostCommentDeletedEvent::class   => 'onPostCommentDeleted',
            PostCommentRestoredEvent::class   => 'onPostCommentRestored',
            MagazineBanEvent::class          => 'onBan',
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

        $log = new MagazineLogEntryDeleted($event->entry, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onEntryRestored(EntryRestoredEvent $event): void
    {
        if ($event->entry->isTrashed()) {
            return;
        }

        if (!$event->user || $event->entry->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryRestored($event->entry, $event->user);

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

        $log = new MagazineLogEntryCommentDeleted($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onEntryCommentRestored(EntryCommentRestoredEvent $event): void
    {
        if ($event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryCommentRestored($event->comment, $event->user);

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

        $log = new MagazineLogPostDeleted($event->post, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onPostRestored(PostRestoredEvent $event): void
    {
        if ($event->post->isTrashed()) {
            return;
        }

        if (!$event->user || $event->post->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostRestored($event->post, $event->user);

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

        $log = new MagazineLogPostCommentDeleted($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onPostCommentRestored(PostCommentRestoredEvent $event): void
    {
        if ($event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostCommentRestored($event->comment, $event->user);

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
