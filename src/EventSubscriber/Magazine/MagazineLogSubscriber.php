<?php declare(strict_types=1);

namespace App\EventSubscriber\Magazine;

use App\Entity\MagazineLogBan;
use App\Entity\MagazineLogEntryCommentDeleted;
use App\Entity\MagazineLogEntryDeleted;
use App\Entity\MagazineLogPostCommentDeleted;
use App\Entity\MagazineLogPostDeleted;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\Magazine\MagazineBanEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
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

        $log = new MagazineLogEntryDeleted($event->entry, $event->user);

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

    public function onBan(MagazineBanEvent $event): void
    {
        $log = new MagazineLogBan($event->ban);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
