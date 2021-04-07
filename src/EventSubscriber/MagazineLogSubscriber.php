<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\MagazineLogBan;
use App\Entity\MagazineLogEntryCommentDelete;
use App\Entity\MagazineLogEntryDelete;
use App\Entity\MagazineLogPostCommentDelete;
use App\Entity\MagazineLogPostDelete;
use App\Event\EntryCommentDeletedEvent;
use App\Event\EntryDeletedEvent;
use App\Event\MagazineBanEvent;
use App\Event\PostCommentDeletedEvent;
use App\Event\PostDeletedEvent;
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

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        if (!$event->getComment()->isTrashed()) {
            return;
        }

        if (!$event->getUser() || $event->getComment()->isAuthor($event->getUser())) {
            return;
        }

        $log = new MagazineLogEntryCommentDelete($event->getComment(), $event->getUser());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onPostDeleted(PostDeletedEvent $event): void
    {
        if (!$event->getPost()->isTrashed()) {
            return;
        }

        if (!$event->getUser() || $event->getPost()->isAuthor($event->getUser())) {
            return;
        }

        $log = new MagazineLogPostDelete($event->getPost(), $event->getUser());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        if (!$event->getComment()->isTrashed()) {
            return;
        }

        if (!$event->getUser() || $event->getComment()->isAuthor($event->getUser())) {
            return;
        }

        $log = new MagazineLogPostCommentDelete($event->getComment(), $event->getUser());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onBan(MagazineBanEvent $event): void
    {
        $log = new MagazineLogBan($event->getBan());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
