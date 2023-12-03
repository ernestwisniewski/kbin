<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\EventSubscriber;

use App\Entity\Notification;
use App\Kbin\Entry\EventSubscriber\Event\EntryHasBeenSeenEvent;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryShowSubscriber
{
    public function __construct(
        private Security $security,
        private NotificationRepository $repository,
    ) {
    }

    #[AsEventListener(event: EntryHasBeenSeenEvent::class)]
    public function readNotifications(EntryHasBeenSeenEvent $event): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $notifications = $this->repository->findUnreadEntryNotifications($this->security->getUser(), $event->entry);

        if (!\count($notifications)) {
            return;
        }

        array_map(fn($notification) => $notification->status = Notification::STATUS_READ, $notifications);
    }
}
