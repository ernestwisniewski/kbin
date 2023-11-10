<?php

declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Entity\Entry;
use App\Entity\Notification;
use App\Event\Entry\EntryHasBeenSeenEvent;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchoulom\ViewCounterBundle\Counter\ViewCounter as Counter;

class EntryShowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Counter $viewCounter,
        private readonly Security $security,
        private readonly NotificationRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[ArrayShape([EntryHasBeenSeenEvent::class => 'string'])]
    public static function getSubscribedEvents(): array
    {
        return [
            EntryHasBeenSeenEvent::class => 'onShowEntry',
        ];
    }

    public function onShowEntry(EntryHasBeenSeenEvent $event): void
    {
        $this->readMessage($event->entry);
    }

    private function readMessage(Entry $entry): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $notifications = $this->repository->findUnreadEntryNotifications($this->security->getUser(), $entry);

        if (!\count($notifications)) {
            return;
        }

        array_map(fn($notification) => $notification->status = Notification::STATUS_READ, $notifications);

        $this->entityManager->flush();
    }
}
