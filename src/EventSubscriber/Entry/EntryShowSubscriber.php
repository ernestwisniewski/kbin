<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

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

readonly class EntryShowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private NotificationRepository $repository,
        private EntityManagerInterface $entityManager
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

        array_map(fn ($notification) => $notification->status = Notification::STATUS_READ, $notifications);

        $this->entityManager->flush();
    }
}
